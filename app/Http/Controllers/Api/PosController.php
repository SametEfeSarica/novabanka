<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentSession;
use App\Models\PosApiClient; // Client modelini ekledik

/**
 * PosController
 *
 * E-ticaret sitelerinin çağırdığı API endpoint'lerini yönetir.
 */
class PosController extends Controller
{
    /**
     * POST /api/v1/pos/create-session
     */
    public function createSession(Request $request)
    {
        // ── Giriş Doğrulama ────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'order_id'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'currency'       => 'required|string|in:TRY,USD,EUR',
            'description'    => 'required|string|max:255',
            'customer_name'  => 'required|string|max:100',
            'customer_email' => 'required|email|max:150',
            'return_url'     => 'required|url|max:500',
            'webhook_url'    => 'required|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'Doğrulama hatası.',
                'details' => $validator->errors(),
                'code'    => 'VALIDATION_ERROR',
            ], 422);
        }

        // ── KRİTİK DÜZELTME: Client Doğrulaması ─────────────────────────────
        // Middleware devre dışı olduğu için veritabanındaki aktif ilk istemciyi (Ensar) alıyoruz.
        $client = PosApiClient::where('is_active', true)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'error'   => 'Sistemde kayıtlı aktif bir API istemcisi bulunamadı.',
                'code'    => 'CLIENT_NOT_FOUND',
            ], 403);
        }
        // ───────────────────────────────────────────────────────────────────

        // ── Aynı sipariş için bekleyen oturum var mı? ──────────────────────
        $existing = PaymentSession::where('client_id', $client->id)
            ->where('order_id', $request->order_id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json([
                'success'       => true,
                'payment_token' => $existing->token,
                'checkout_url'  => route('checkout.show', $existing->token),
                'expires_at'    => $existing->expires_at->toIso8601String(),
            ]);
        }

        // ── Yeni Ödeme Oturumu Oluştur ─────────────────────────────────────
        $token = 'tok_' . Str::random(32); 

        $session = PaymentSession::create([
            'client_id'      => $client->id,
            'token'          => $token,
            'order_id'       => $request->order_id,
            'amount'         => $request->amount,
            'currency'       => $request->currency,
            'description'    => $request->description,
            'customer_name'  => $request->customer_name,
            'customer_email' => $request->customer_email,
            'return_url'     => $request->return_url,
            'webhook_url'    => $request->webhook_url,
            'status'         => 'pending',
            'expires_at'     => now()->addMinutes(30),
        ]);

        Log::info('Nova POS: Yeni ödeme oturumu oluşturuldu', [
            'session_id' => $session->id,
            'client_id'  => $client->id,
            'order_id'   => $request->order_id,
            'amount'     => $request->amount,
        ]);

        return response()->json([
            'success'       => true,
            'payment_token' => $token,
            'checkout_url'  => route('checkout.show', $token),
            'expires_at'    => $session->expires_at->toIso8601String(),
        ], 201);
    }

    /**
     * GET /api/v1/pos/session/{token}/status
     */
    public function getSessionStatus(Request $request, string $token)
    {
        $client = PosApiClient::where('is_active', true)->first();
        
        $session = PaymentSession::where('token', $token)
            ->where('client_id', $client->id) 
            ->first();

        if (! $session) {
            return response()->json([
                'success' => false,
                'error'   => 'Oturum bulunamadı.',
                'code'    => 'SESSION_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success'    => true,
            'order_id'   => $session->order_id,
            'amount'     => $session->amount,
            'currency'   => $session->currency,
            'status'     => $session->status,
            'expires_at' => $session->expires_at->toIso8601String(),
        ]);
    }
}
