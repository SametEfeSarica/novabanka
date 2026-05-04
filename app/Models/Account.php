<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'user_id', 'iban', 'account_number', 'currency',
        'balance', 'account_type', 'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'sender_account_id');
    }

    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'receiver_account_id');
    }

    public function getFormattedIbanAttribute(): string
    {
        return implode(' ', str_split($this->iban, 4));
    }

    public function deposit(float $amount): bool
    {
        $this->increment('balance', $amount);
        return true;
    }

    public function withdraw(float $amount): bool
    {
        if ($this->balance < $amount) {
            return false;
        }
        $this->decrement('balance', $amount);
        return true;
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }
}