<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\PaymentSession;
use App\Models\Transaction;

/**
 * CheckoutController
 *
 * Kullanıcının tarayıcısında çalışan ödeme akışını yönetir:
 *  1. Ödeme formunu göster  (show)
 *  2. Kartı işle & webhook at (process)
 *  3. Sonuç sayfaları        (success / failed)
 */
class CheckoutController extends Controller
{
    /**
     * GET /checkout/{token}
     *
     * Token'ı doğrula, geçerliyse ödeme formunu göster.
     */
    public function show(string $token)
    {
        $session = $this->findValidSession($token);

        if (! $session) {
            return view('checkout.expired', ['reason' => 'Ödeme bağlantısı geçersiz veya süresi dolmuş.']);
        }

        return view('checkout.form', compact('session'));
    }

    /**
     * POST /checkout/{token}/process
     *
     * Kart bilgilerini doğrula → işlem yap → webhook at → yönlendir.
     */
    public function process(Request $request, string $token)
    {
        $session = $this->findValidSession($token);

        if (! $session) {
            return redirect()->route('checkout.failed')
                ->with('error', 'Ödeme oturumu geçersiz veya süresi dolmuş.');
        }

        // ── 1. Form Doğrulama ──────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'card_number' => ['required', 'digits:16'],
            'card_holder' => ['required', 'string', 'min:3', 'max:60'],
            'expiry_month'=> ['required', 'digits:2', 'between:1,12'],
            'expiry_year' => ['required', 'digits:4', 'gte:' . date('Y')],
            'cvv'         => ['required', 'digits_between:3,4'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except(['cvv'])); // CVV asla geri gönderilmez
        }

        // ── 2. Kart Bilgilerini Doğrula (Simüle) ─────────────────────────
        // GERÇEK PROJEDE: Burada bankanın çekirdek sistemiyle konuşulur.
        // LOCALHOST TESTİ: Basit kural: 4444 ile başlayan kartlar başarısız.
        $cardNumber = $request->card_number;
        $isSuccess  = ! str_starts_with($cardNumber, '4444');

        // ── 3. İşlem Kaydını Oluştur ───────────────────────────────────────
        $transaction = PosTransaction::create([
            'session_id'       => $session->id,
            'card_last_four'   => substr($cardNumber, -4),
            'card_holder'      => $request->card_holder,
            'amount'           => $session->amount,
            'currency'         => $session->currency,
            'status'           => $isSuccess ? 'completed' : 'failed',
            'failure_reason'   => $isSuccess ? null : 'Kart reddedildi.',
            'processed_at'     => now(),
        ]);

        // ── 4. Oturum Durumunu Güncelle ────────────────────────────────────
        $session->update([
            'status'         => $isSuccess ? 'completed' : 'failed',
            'transaction_id' => $transaction->id,
        ]);

        // ── 5. E-ticaret Sitesine Webhook Gönder ──────────────────────────
        if ($isSuccess) {
            $this->sendWebhook($session, $transaction);
        }

        // ── 6. Kullanıcıyı E-ticaret Return URL'ine Yönlendir ─────────────
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
     * E-ticaret sitesine arka planda webhook gönderir.
     * Başarısız olursa job queue ile tekrar denenir (opsiyonel).
     */
    private function sendWebhook(PaymentSession $session, Transaction $transaction): void
    {
        // Webhook payload'ı
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

        // HMAC imzası — e-ticaret tarafı bunu doğrulayacak
        $webhookSecret = $session->posClient->webhook_secret;
        $signature     = hash_hmac('sha256', json_encode($payload), $webhookSecret);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type'       => 'application/json',
                    'X-Nova-Signature'   => $signature,
                    'X-Nova-Event'       => 'payment.completed',
                ])
                ->post($session->webhook_url, $payload);

            Log::info('Nova POS: Webhook gönderildi', [
                'session_id'  => $session->id,
                'webhook_url' => $session->webhook_url,
                'status_code' => $response->status(),
            ]);

        } catch (\Exception $e) {
            // Webhook başarısız olsa da kullanıcı akışı etkilenmez
            Log::error('Nova POS: Webhook gönderilemedi', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
            ]);
            // TODO: Gerçek projede buraya bir RetryWebhookJob dispatch edilir
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
