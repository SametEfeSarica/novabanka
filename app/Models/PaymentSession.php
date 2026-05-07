<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * PaymentSession — Ödeme Oturumu
 *
 * E-ticaret sitesinden gelen ödeme başlatma isteğini temsil eder.
 * Her oturum tek kullanımlıktır ve 30 dakika geçerlidir.
 *
 * @property int         $id
 * @property int         $client_id         PosApiClient FK
 * @property string      $token             Benzersiz ödeme token'ı
 * @property string      $order_id          E-ticaret sipariş ID'si
 * @property float       $amount            Ödeme tutarı
 * @property string      $currency          TRY / USD / EUR
 * @property string      $description       Ödeme açıklaması
 * @property string      $customer_name
 * @property string      $customer_email
 * @property string      $return_url        Ödeme sonrası yönlendirme
 * @property string      $webhook_url       Bildirim URL'i
 * @property string      $status            pending|completed|failed|expired
 * @property int|null    $transaction_id    Transaction FK (tamamlandıktan sonra)
 * @property Carbon      $expires_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class PaymentSession extends Model
{
    use HasFactory;

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
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
