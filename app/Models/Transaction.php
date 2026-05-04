<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'sender_account_id', 'receiver_account_id', 'type',
        'amount', 'fee', 'currency', 'description',
        'reference_no', 'status', 'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'metadata' => 'array',
    ];

    const TYPE_TRANSFER   = 'transfer';
    const TYPE_DEPOSIT    = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_PAYMENT    = 'payment';
    const TYPE_EXCHANGE   = 'exchange';

    public function senderAccount()
    {
        return $this->belongsTo(Account::class, 'sender_account_id');
    }

    public function receiverAccount()
    {
        return $this->belongsTo(Account::class, 'receiver_account_id');
    }

    public static function generateReferenceNo(): string
    {
        return 'NVB' . date('ymd') . strtoupper(substr(uniqid(), -8));
    }

    public function getTypeLabel(): string
    {
        $labels = [
            'transfer'   => 'Havale/EFT',
            'deposit'    => 'Para Yatırma',
            'withdrawal' => 'Para Çekme',
            'payment'    => 'Alışveriş',
            'exchange'   => 'Döviz/Kripto',
        ];
        return $labels[$this->type] ?? $this->type;
    }
}