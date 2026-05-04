<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'user_id', 'account_id', 'card_number', 'card_holder_name',
        'expiry_month', 'expiry_year', 'cvv', 'card_type',
        'card_brand', 'spending_limit', 'is_active', 'is_frozen',
        'online_shopping', 'contactless'
    ];

    // protected $hidden = ['cvv'];

    protected $casts = [
        'is_active'      => 'boolean',
        'is_frozen'      => 'boolean',
        'online_shopping'=> 'boolean',
        'contactless'    => 'boolean',
        'spending_limit' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getMaskedNumberAttribute(): string
    {
        return '**** **** **** ' . substr($this->card_number, -4);
    }

    public function getExpiryDateAttribute(): string
    {
        return $this->expiry_month . '/' . $this->expiry_year;
    }

    public function isExpired(): bool
    {
        $expiry = \Carbon\Carbon::createFromDate($this->expiry_year, $this->expiry_month, 1);
        return $expiry->isPast();
    }
}