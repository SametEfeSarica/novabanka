<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NovaBankaService;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * NovaWebhookController
 *
 * Nova Banka'nın arka planda gönderdiği ödeme bildirimlerini karşılar.
 *
 * Bu endpoint CSRF korumasından muaf tutulmalıdır!
 * app/Http/Middleware/VerifyCsrfToken.php → $except dizisine ekle:
 *   'webhook/nova'
 *
 * Ayrıca routes/web.php'ye (veya api.php'ye) eklenmelidir:
 *   Route::post('/webhook/nova', [NovaWebhookController::class, 'handle'])
 *       ->name('webhook.nova');
 */
class NovaWebhookController extends Controller
{
    public function __construct(
        private readonly NovaBankaService $novaService
    ) {}

    /**
     * POST /webhook/nova
     *
     * Nova Banka'dan gelen payload:
     * {
     *   "event"          : "payment.completed",
     *   "payment_token"  : "tok_xxx",
     *   "order_id"       : "12345",
     *   "amount"         : 249.90,
     *   "currency"       : "TRY",
     *   "transaction_id" : 999,
     *   "card_last_four" : "1234",
     *   "processed_at"   : "2025-01-01T12:00:00Z"
     * }
     *
     * Nova Banka imzayı header olarak gönderir:
     *   X-Nova-Signature: <hmac-sha256>
     *   X-Nova-Event: payment.completed
     */
    public function handle(Request $request)
    {
        // ── 1. İmza Doğrulama ─────────────────────────────────────────────
        if (! $this->novaService->verifyWebhookSignature($request)) {
            Log::warning('Nova Webhook: Geçersiz imza', [
                'ip'        => $request->ip(),
                'signature' => $request->header('X-Nova-Signature'),
            ]);
            // 401 dön — Nova Banka tekrar deneyebilir
            return response()->json(['error' => 'Geçersiz imza.'], 401);
        }

        $event   = $request->header('X-Nova-Event');
        $payload = $request->json()->all();

        Log::info('Nova Webhook: Alındı', [
            'event'    => $event,
            'order_id' => $payload['order_id'] ?? null,
            'token'    => $payload['payment_token'] ?? null,
        ]);

        // ── 2. Event Tipine Göre İşle ──────────────────────────────────────
        match ($event) {
            'payment.completed' => $this->handlePaymentCompleted($payload),
            default             => Log::info("Nova Webhook: Bilinmeyen event: {$event}"),
        };

        // Her durumda 200 dön — Nova Banka'nın tekrar denemesini önler
        return response()->json(['received' => true], 200);
    }

    /**
     * Başarılı ödeme bildirimini işler.
     */
    private function handlePaymentCompleted(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $token   = $payload['payment_token'] ?? null;
        $amount  = $payload['amount'] ?? null;

        if (! $orderId || ! $token) {
            Log::error('Nova Webhook: Eksik payload', $payload);
            return;
        }

        // Siparişi token ile birlikte bul (çifte güvenlik)
        $order = Order::where('id', $orderId)
            ->where('payment_token', $token)
            ->first();

        if (! $order) {
            Log::error('Nova Webhook: Sipariş bulunamadı', [
                'order_id' => $orderId,
                'token'    => $token,
            ]);
            return;
        }

        // İdempotency: Zaten ödendi ise tekrar işleme
        if ($order->payment_status === 'paid') {
            Log::info('Nova Webhook: Sipariş zaten ödenmiş, atlandı', [
                'order_id' => $orderId,
            ]);
            return;
        }

        // Tutarı doğrula (manipülasyon koruması)
        if ((float) $amount !== (float) $order->total_amount) {
            Log::error('Nova Webhook: Tutar uyuşmazlığı!', [
                'order_id'       => $orderId,
                'beklenen'       => $order->total_amount,
                'gelen'          => $amount,
            ]);
            return;
        }

        // ── Siparişi Güncelle ─────────────────────────────────────────────
        $order->update([
            'payment_status'       => 'paid',
            'nova_transaction_id'  => $payload['transaction_id'] ?? null,
            'paid_at'              => now(),
            'card_last_four'       => $payload['card_last_four'] ?? null,
        ]);

        // ── Sipariş İşleme Tetikle ────────────────────────────────────────
        // TODO: Stok düş, kargo oluştur, bildirim gönder vb.
        // event(new OrderPaid($order));

        Log::info('Nova Webhook: Sipariş ödendi ve güncellendi', [
            'order_id'      => $orderId,
            'transaction_id'=> $payload['transaction_id'] ?? null,
        ]);
    }
}
