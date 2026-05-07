<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * PosApiClient — Yetkili E-Ticaret Sitesi
 *
 * Nova Banka'ya bağlanan her e-ticaret sitesi bu tabloda kayıtlıdır.
 * Her kaydın bir API Key ve Secret'i vardır.
 *
 * Yeni kayıt eklemek için: php artisan pos:create-client {site_adı}
 *
 * @property int     $id
 * @property string  $name           Site adı (ör: "Ahmet'in Marketi")
 * @property string  $api_key        X-POS-API-KEY header değeri
 * @property string  $api_secret     HMAC imza için gizli anahtar
 * @property string  $webhook_secret Webhook imzası için gizli anahtar
 * @property bool    $is_active
 */
class PosApiClient extends Model
{
    use HasFactory;

    protected $table = 'pos_api_clients';

    protected $fillable = [
        'name', 'api_key', 'api_secret', 'webhook_secret', 'is_active',
    ];

    protected $hidden = [
        'api_secret', 'webhook_secret', // JSON yanıtta gizle
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sessions()
    {
        return $this->hasMany(PaymentSession::class, 'client_id');
    }
}
