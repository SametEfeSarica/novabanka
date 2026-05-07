<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NovaBankaService;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * OrderPaymentController
 *
 * Ödeme başlatma ve return URL işlemlerini yönetir.
 */
class OrderPaymentController extends Controller
{
    public function __construct(
        private readonly NovaBankaService $novaService
    ) {}

    /**
     * POST /orders/{order}/pay
     *
     * Kullanıcı "Ödeme Yap" butonuna bastığında çağrılır.
     * Nova Banka'da oturum açar ve kullanıcıyı ödeme sayfasına yönlendirir.
     */
    public function initiate(Request $request, Order $order)
    {
        // Siparişin bu kullanıcıya ait olduğunu ve ödeme beklendiğini doğrula
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Bu sipariş için ödeme başlatılamaz.');
        }

        try {
            $result = $this->novaService->createPaymentSession([
                'order_id'       => (string) $order->id,
                'amount'         => $order->total_amount,
                'currency'       => 'TRY',
                'description'    => "Sipariş #{$order->id} — {$order->item_count} ürün",
                'customer_name'  => auth()->user()->name,
                'customer_email' => auth()->user()->email,
                'return_url'     => route('orders.payment-return'),
                'webhook_url'    => route('webhook.nova'),
            ]);

            // Token'ı siparişe kaydet (return URL'de doğrulama için)
            $order->update([
                'payment_token'  => $result['payment_token'],
                'payment_status' => 'awaiting_payment',
            ]);

            // Kullanıcıyı Nova Banka ödeme sayfasına gönder
            return redirect()->away($result['checkout_url']);

        } catch (\RuntimeException $e) {
            Log::error('Ödeme başlatma hatası', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Ödeme sayfasına yönlendirilemedi. Lütfen tekrar deneyin.');
        }
    }

    /**
     * GET /orders/payment-return
     *
     * Nova Banka ödeme sonrasında kullanıcıyı bu sayfaya gönderir.
     * Query params: payment_token, order_id, status
     *
     * NOT: Gerçek sipariş onayı WebhookController'da yapılır!
     * Bu sayfa sadece kullanıcıya bilgi vermek için gösterilir.
     */
    public function returnFromBank(Request $request)
    {
        $token    = $request->query('payment_token');
        $orderId  = $request->query('order_id');
        $status   = $request->query('status'); // 'success' veya 'failed'

        if (! $token || ! $orderId) {
            return redirect()->route('home')->with('error', 'Geçersiz yönlendirme.');
        }

        $order = Order::where('id', $orderId)
            ->where('payment_token', $token)
            ->where('user_id', auth()->id())
            ->first();

        if (! $order) {
            return redirect()->route('home')->with('error', 'Sipariş bulunamadı.');
        }

        if ($status === 'success') {
            // Webhook zaten geldi mi? (webhook genellikle return'den önce gelir)
            if ($order->payment_status === 'paid') {
                return redirect()->route('orders.show', $order)
                    ->with('success', '✓ Ödemeniz başarıyla alındı!');
            }

            // Webhook henüz gelmedi, "işleniyor" mesajı göster
            // Gerçek projede burada polling veya JS reload yapılabilir
            return view('orders.payment_processing', compact('order'));
        }

        // Ödeme başarısız
        $order->update(['payment_status' => 'failed']);
        return redirect()->route('orders.show', $order)
            ->with('error', 'Ödeme işlemi başarısız. Lütfen tekrar deneyin.');
    }
}
