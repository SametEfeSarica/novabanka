<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\PaymentSession;
use App\Models\PosTransaction;
use App\Models\Account;
use App\Services\TransferService;

/**
 * CheckoutController
 *
 * Kullanıcının tarayıcısında çalışan ödeme akışını yönetir:
 *  1. Ödeme formunu göster  (show)
 *  2. Kartı işle & IBAN transferi yap & webhook at (process)
 *  3. Sonuç sayfaları (success / failed)
 */
class CheckoutController extends Controller
{
    public function __construct(private TransferService $transferService) {}

    /**
     * GET /checkout/{token}
     */
    public function show(string $token)
    {
        $session = $this->findValidSession($token);

        if (!$session) {
            return view('checkout.expired', ['reason' => 'Ödeme bağlantısı geçersiz veya süresi dolmuş.']);
        }

        return view('checkout.form', compact('session'));
    }

    /**
     * POST /checkout/{token}/process
     *
     * Kart bilgilerini doğrula → karttan düş → satıcı IBAN'ına transfer et
     * → webhook at → kullanıcıyı yönlendir.
     */
    public function process(Request $request, string $token)
    {
        $session = $this->findValidSession($token);

        if (!$session) {
            return redirect()->route('checkout.failed')
                ->with('error', 'Ödeme oturumu geçersiz veya süresi dolmuş.');
        }

        // ── 1. Form Doğrulama ──────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'card_number'  => ['required', 'digits:16'],
            'card_holder'  => ['required', 'string', 'min:3', 'max:60'],
            'expiry_month' => ['required', 'digits:2', 'between:1,12'],
            'expiry_year'  => ['required', 'digits:4', 'gte:' . date('Y')],
            'cvv'          => ['required', 'digits_between:3,4'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except(['cvv']));
        }

        // ── 2. Kartı Bul ve Doğrula ────────────────────────────────────────
        $cardNumber = $request->card_number;

        $paymentResult = $this->transferService->processCardPayment(
            cardNumber:   $cardNumber,
            expiryMonth:  $request->expiry_month,
            expiryYear:   $request->expiry_year,
            cvv:          $request->cvv,
            amount:       $session->amount,
            merchantName: $session->description,
        );

        $isSuccess = $paymentResult['success'];

        // ── 3. İşlem Kaydını Oluştur ───────────────────────────────────────
        $transaction = PosTransaction::create([
            'session_id'     => $session->id,
            'card_last_four' => substr($cardNumber, -4),
            'card_holder'    => $request->card_holder,
            'amount'         => $session->amount,
            'currency'       => $session->currency,
            'status'         => $isSuccess ? 'completed' : 'failed',
            'failure_reason' => $isSuccess ? null : ($paymentResult['message'] ?? 'Kart reddedildi.'),
            'processed_at'   => now(),
        ]);

        // ── 4. Oturum Durumunu Güncelle ────────────────────────────────────
        $session->update([
            'status'         => $isSuccess ? 'completed' : 'failed',
            'transaction_id' => $transaction->id,
        ]);

        // ── 5. Başarılıysa: Satıcı IBAN'ına Transfer Et ───────────────────
        if ($isSuccess && $session->seller_iban) {
            $this->transferToSeller($session);
        }

        // ── 6. E-ticaret Sitesine Webhook Gönder ──────────────────────────
        if ($isSuccess) {
            $this->sendWebhook($session, $transaction);
        }

        // ── 7. Kullanıcıyı Return URL'e Yönlendir ─────────────────────────
        $returnParams = [
            'payment_token' => $token,
            'order_id'      => $session->order_id,
            'status'        => $isSuccess ? 'success' : 'failed',
        ];

        $returnUrl = $session->return_url . '?' . http_build_query($returnParams);

        Log::info('Nova POS: Ödeme işlendi', [
            'session_id'     => $session->id,
            'transaction_id' => $transaction->id,
            'status'         => $transaction->status,
        ]);

        return redirect()->away($returnUrl);
    }

    /**
     * Ödeme başarılıysa satıcının IBAN'ına para aktarır.
     *
     * Banka'nın sistem hesabı (kart ödemesinden gelen para zaten bu hesapta)
     * satıcının IBAN'ına TransferService aracılığıyla gönderilir.
     */
    private function transferToSeller(PaymentSession $session): void
    {
        $sellerAccount = Account::where('iban', $session->seller_iban)->first();

        if (!$sellerAccount) {
            Log::error('Nova POS: Satıcı IBAN\'ı bulunamadı, transfer yapılamadı!', [
                'session_id'  => $session->id,
                'seller_iban' => $session->seller_iban,
            ]);
            return;
        }

        try {
            // Para doğrudan satıcının hesabına yatırılır.
            // (processCardPayment zaten alıcının hesabından düştü;
            //  burada o tutarı satıcıya deposit ediyoruz.)
            $sellerAccount->deposit($session->amount);

            Log::info('Nova POS: Satıcıya transfer tamamlandı', [
                'session_id'  => $session->id,
                'seller_iban' => '***' . substr($session->seller_iban, -4),
                'amount'      => $session->amount,
                'currency'    => $session->currency,
            ]);

        } catch (\Exception $e) {
            Log::error('Nova POS: Satıcıya transfer başarısız!', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * E-ticaret sitesine arka planda webhook gönderir.
     */
    private function sendWebhook(PaymentSession $session, PosTransaction $transaction): void
    {
        $payload = [
            'event'          => 'payment.completed',
            'payment_token'  => $session->token,
            'order_id'       => $session->order_id,
            'amount'         => $session->amount,
            'currency'       => $session->currency,
            'transaction_id' => $transaction->id,
            'card_last_four' => $transaction->card_last_four,
            'processed_at'   => $transaction->processed_at->toIso8601String(),
        ];

        $webhookSecret = $session->posClient->webhook_secret;
        $signature     = hash_hmac('sha256', json_encode($payload), $webhookSecret);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type'     => 'application/json',
                    'X-Nova-Signature' => $signature,
                    'X-Nova-Event'     => 'payment.completed',
                ])
                ->post($session->webhook_url, $payload);

            Log::info('Nova POS: Webhook gönderildi', [
                'session_id'  => $session->id,
                'status_code' => $response->status(),
            ]);

        } catch (\Exception $e) {
            Log::error('Nova POS: Webhook gönderilemedi', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Geçerli ve süresi dolmamış bir PaymentSession getirir.
     */
    private function findValidSession(string $token): ?PaymentSession
    {
        return PaymentSession::with('posClient')
            ->where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function success()
    {
        return view('checkout.success');
    }

    public function failed()
    {
        return view('checkout.failed');
    }
}
