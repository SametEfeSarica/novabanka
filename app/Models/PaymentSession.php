<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon; // EKLENDİ: Tarih işlemleri için gerekli

/**
 * PaymentSession — Ödeme Oturumu
 */
class PaymentSession extends Model
{
    use HasFactory;

    // KRİTİK DÜZELTME: SQL ile oluşturulan tablo ismini tanımladık
    protected $table = 'payment_sessions';

    protected $fillable = [
        'client_id', 'token', 'order_id', 'amount', 'currency',
        'description', 'customer_name', 'customer_email',
        'return_url', 'webhook_url', 'status', 'transaction_id',
        'expires_at',
    ];

    protected $casts = [
        'amount'     => 'float',
        'expires_at' => 'datetime',
    ];

    // ── İlişkiler ──────────────────────────────────────────────────────────

    /** Bu oturumun sahibi e-ticaret sitesi */
    public function posClient()
    {
        return $this->belongsTo(PosApiClient::class, 'client_id');
    }

    /** Bu oturuma bağlı işlem kaydı */
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'session_id');
    }

    // ── Yardımcı Metodlar ──────────────────────────────────────────────────

    public function isExpired(): bool
    {
        // Null kontrolü eklendi, expires_at boşsa süresi dolmuş sayar
        return $this->expires_at ? $this->expires_at->isPast() : true;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
