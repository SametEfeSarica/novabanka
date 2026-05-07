<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PosApiClient;
use Illuminate\Support\Facades\Log;

/**
 * PosAuthMiddleware
 *
 * Nova Banka API'sine gelen her isteği doğrular.
 * E-ticaret siteleri her istekte header olarak şunları göndermek zorundadır:
 *
 *   X-POS-API-KEY    : Kayıtlı API anahtarı
 *   X-POS-SIGNATURE  : HMAC-SHA256 imzası
 *
 * İmza hesaplama (e-ticaret tarafında):
 *   $signature = hash_hmac('sha256', $requestBody, $apiSecret);
 */
class PosAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey   = $request->header('X-POS-API-KEY');
        $signature = $request->header('X-POS-SIGNATURE');

        // ── 1. Header varlık kontrolü ──────────────────────────────────────
        if (! $apiKey || ! $signature) {
            return response()->json([
                'success' => false,
                'error'   => 'Eksik kimlik doğrulama başlıkları.',
                'code'    => 'MISSING_AUTH_HEADERS',
            ], 401);
        }

        // ── 2. API Key'i veritabanında ara ─────────────────────────────────
        $client = PosApiClient::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (! $client) {
            Log::warning('Nova POS: Geçersiz API Key denemesi', [
                'api_key' => $apiKey,
                'ip'      => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => 'Geçersiz API anahtarı.',
                'code'    => 'INVALID_API_KEY',
            ], 401);
        }

        // ── 3. HMAC İmza Doğrulaması ───────────────────────────────────────
        // E-ticaret sitesi: hash_hmac('sha256', rawBody, apiSecret)
        $expectedSignature = hash_hmac(
            'sha256',
            $request->getContent(), // Ham request body
            $client->api_secret
        );

        // Timing-safe karşılaştırma (timing attack'a karşı)
        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('Nova POS: İmza doğrulaması başarısız', [
                'client_id' => $client->id,
                'ip'        => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => 'İmza doğrulaması başarısız.',
                'code'    => 'INVALID_SIGNATURE',
            ], 401);
        }

        // ── 4. Client bilgisini request'e ekle ────────────────────────────
        $request->merge(['_pos_client' => $client]);

        return $next($request);
    }
}
