<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NovaBankaService
 *
 * E-ticaret sitesinin Nova Banka Sanal POS API'siyle iletişim kurmasını sağlar.
 *
 * Yapılandırma (.env dosyasına eklenecekler):
 *   NOVA_BANKA_API_URL      = http://localhost/novabanka/api/v1/pos
 *   NOVA_BANKA_API_KEY      = nb_key_xxxxxxxxxxxxxx
 *   NOVA_BANKA_API_SECRET   = nb_secret_xxxxxxxxxxxxxx
 *   NOVA_BANKA_WEBHOOK_SECRET = nb_whsec_xxxxxxxxxxxxxx
 */
class NovaBankaService
{
    private string $apiUrl;
    private string $apiKey;
    private string $apiSecret;
    private string $webhookSecret;

    public function __construct()
    {
        $this->apiUrl        = rtrim(config('services.nova_banka.api_url'), '/');
        $this->apiKey        = config('services.nova_banka.api_key');
        $this->apiSecret     = config('services.nova_banka.api_secret');
        $this->webhookSecret = config('services.nova_banka.webhook_secret');
    }

    /**
     * Nova Banka'da bir ödeme oturumu oluşturur ve checkout URL'ini döner.
     *
     * @param array $orderData Sipariş bilgileri
     * @return array ['payment_token' => '...', 'checkout_url' => '...', 'expires_at' => '...']
     *
     * @throws \RuntimeException API hatası durumunda
     *
     * Kullanım örneği (Controller'da):
     *   $result = app(NovaBankaService::class)->createPaymentSession([
     *       'order_id'       => 'ORD-12345',
     *       'amount'         => 249.90,
     *       'currency'       => 'TRY',
     *       'description'    => 'Sipariş #12345',
     *       'customer_name'  => $order->customer_name,
     *       'customer_email' => $order->customer_email,
     *       'return_url'     => route('orders.payment-return'),
     *       'webhook_url'    => route('webhook.nova'),
     *   ]);
     *   return redirect()->away($result['checkout_url']);
     */
    public function createPaymentSession(array $orderData): array
    {
        $endpoint = $this->apiUrl . '/create-session';
        $body     = json_encode($orderData);

        // HMAC-SHA256 imzası oluştur
        $signature = hash_hmac('sha256', $body, $this->apiSecret);

        Log::info('NovaBanka: Ödeme oturumu oluşturuluyor', [
            'order_id' => $orderData['order_id'] ?? null,
            'amount'   => $orderData['amount'] ?? null,
        ]);

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type'    => 'application/json',
                    'Accept'          => 'application/json',
                    'X-POS-API-KEY'   => $this->apiKey,
                    'X-POS-SIGNATURE' => $signature,
                ])
                ->withBody($body, 'application/json')
                ->post($endpoint);

            $data = $response->json();

            if (! $response->successful() || ! ($data['success'] ?? false)) {
                $error = $data['error'] ?? 'Bilinmeyen hata';
                Log::error('NovaBanka: Oturum oluşturulamadı', [
                    'status' => $response->status(),
                    'error'  => $error,
                ]);
                throw new \RuntimeException("Nova Banka hatası: {$error}");
            }

            Log::info('NovaBanka: Oturum oluşturuldu', [
                'payment_token' => $data['payment_token'],
            ]);

            return [
                'payment_token' => $data['payment_token'],
                'checkout_url'  => $data['checkout_url'],
                'expires_at'    => $data['expires_at'],
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('NovaBanka: Bağlantı hatası', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Nova Banka servisine ulaşılamıyor. Lütfen tekrar deneyin.');
        }
    }

    /**
     * Bir ödeme oturumunun mevcut durumunu sorgular.
     *
     * @param string $token
     * @return string  'pending' | 'completed' | 'failed' | 'expired'
     */
    public function getSessionStatus(string $token): string
    {
        $endpoint = $this->apiUrl . "/session/{$token}/status";
        $body     = ''; // GET isteği, body yok

        $signature = hash_hmac('sha256', $body, $this->apiSecret);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept'          => 'application/json',
                    'X-POS-API-KEY'   => $this->apiKey,
                    'X-POS-SIGNATURE' => $signature,
                ])
                ->get($endpoint);

            $data = $response->json();

            return $data['status'] ?? 'unknown';

        } catch (\Exception $e) {
            Log::warning('NovaBanka: Durum sorgulanamadı', ['error' => $e->getMessage()]);
            return 'unknown';
        }
    }

    /**
     * Nova Banka'dan gelen webhook'un imzasını doğrular.
     *
     * E-ticaret WebhookController'ında çağırılır:
     *   if (! $this->novaService->verifyWebhookSignature($request)) {
     *       abort(401);
     *   }
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function verifyWebhookSignature(\Illuminate\Http\Request $request): bool
    {
        $received = $request->header('X-Nova-Signature');
        if (! $received) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $this->webhookSecret);

        return hash_equals($expected, $received);
    }
}
