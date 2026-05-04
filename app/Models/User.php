<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'surname', 'email', 'phone', 'tc_no', 
        'birth_date', 'password', 'profile_photo',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    public function getTotalBalanceAttribute(): float
    {
        return $this->accounts->sum('balance');
    }

    public function primaryAccount()
    {
        return $this->accounts()->where('currency', 'TRY')->first();
    }
}