<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Transaction — Ödeme İşlem Kaydı
 *
 * Her ödeme girişiminin kalıcı kaydıdır.
 * Kart numarasının son 4 hanesi saklanır, tam numara ASLA kaydedilmez.
 *
 * @property int         $id
 * @property int         $session_id      PaymentSession FK
 * @property string      $card_last_four  Son 4 hane
 * @property string      $card_holder     Kart sahibi adı
 * @property float       $amount
 * @property string      $currency
 * @property string      $status          completed|failed
 * @property string|null $failure_reason
 * @property Carbon      $processed_at
 */
class PosTransaction extends Model
{
    use HasFactory;

    protected $table = 'pos_transactions';

    protected $fillable = [
        'session_id', 'card_last_four', 'card_holder',
        'amount', 'currency', 'status', 'failure_reason', 'processed_at',
    ];

    protected $casts = [
        'amount'       => 'float',
        'processed_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(PaymentSession::class, 'session_id');
    }
}
