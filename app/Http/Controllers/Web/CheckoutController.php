<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentSession;
use App\Models\PosTransaction;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\TransferService;

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

        // ── 2. Kartı Doğrula ve Ödemeyi Al ────────────────────────────────
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

        // ── 3. POS İşlem Kaydı ─────────────────────────────────────────────
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

        // ── 5. Başarılıysa: Satıcıya Transfer Et ──────────────────────────
        if ($isSuccess && $session->seller_iban) {
            $this->transferToSeller($session);
        }

        // ── 6. Webhook Gönder ──────────────────────────────────────────────
        if ($isSuccess) {
            $this->sendWebhook($session, $transaction);
        }

        // ── 7. Yönlendir ───────────────────────────────────────────────────
        $returnUrl = $session->return_url . '?' . http_build_query([
            'payment_token' => $token,
            'order_id'      => $session->order_id,
            'status'        => $isSuccess ? 'success' : 'failed',
        ]);

        return redirect()->away($returnUrl);
    }

    /**
     * Satıcının IBAN'ına para aktarır ve Transaction kaydı oluşturur.
     */
    private function transferToSeller(PaymentSession $session): void
    {
        $sellerAccount = Account::where('iban', $session->seller_iban)->first();

        if (!$sellerAccount) {
            Log::error('Nova POS: Satıcı IBAN bulunamadı', [
                'session_id'  => $session->id,
                'seller_iban' => $session->seller_iban,
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($session, $sellerAccount) {
                // Bakiyeyi artır
                $sellerAccount->deposit($session->amount);

                // Hesap hareketlerine kaydet (+ olarak gözükür)
                Transaction::create([
                    'sender_account_id'   => null,
                    'receiver_account_id' => $sellerAccount->id,
                    'type'                => Transaction::TYPE_DEPOSIT,
                    'amount'              => $session->amount,
                    'currency'            => $session->currency,
                    'description'         => 'Online Satış: ' . $session->description,
                    'reference_no'        => Transaction::generateReferenceNo(),
                    'status'              => 'completed',
                ]);
            });

            Log::info('Nova POS: Satıcıya transfer tamamlandı', [
                'session_id'  => $session->id,
                'seller_iban' => '***' . substr($session->seller_iban, -4),
                'amount'      => $session->amount,
            ]);

        } catch (\Exception $e) {
            Log::error('Nova POS: Satıcıya transfer başarısız!', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * E-ticaret sitesine webhook gönderir.
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
            Http::timeout(10)
                ->withHeaders([
                    'Content-Type'     => 'application/json',
                    'X-Nova-Signature' => $signature,
                    'X-Nova-Event'     => 'payment.completed',
                ])
                ->post($session->webhook_url, $payload);

        } catch (\Exception $e) {
            Log::error('Nova POS: Webhook gönderilemedi', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

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
