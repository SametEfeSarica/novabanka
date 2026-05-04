<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'user_id', 'account_id', 'symbol', 'type',
        'quantity', 'buy_price', 'current_price', 'total_invested'
    ];

    protected $casts = [
        'quantity'       => 'decimal:8',
        'buy_price'      => 'decimal:8',
        'current_price'  => 'decimal:8',
        'total_invested' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getCurrentValueAttribute(): float
    {
        return $this->quantity * ($this->current_price ?? $this->buy_price);
    }

    public function getProfitLossAttribute(): float
    {
        return $this->current_value - $this->total_invested;
    }

    public function getProfitLossPercentAttribute(): float
    {
        if ($this->total_invested == 0) return 0;
        return (($this->current_value - $this->total_invested) / $this->total_invested) * 100;
    }
}