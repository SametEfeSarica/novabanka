<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\PosApiClient;

/**
 * Nova Banka — Yeni POS İstemcisi Oluşturma Komutu
 *
 * Kullanım:
 *   php artisan pos:create-client "Ahmet'in Marketi"
 *
 * Çıktı:
 *   ✓ İstemci oluşturuldu: Ahmet'in Marketi
 *   API Key    : nb_key_xxxxxxxxxxxxxxxxxxx
 *   API Secret : nb_secret_xxxxxxxxxxxxxxxxx
 *   Webhook Secret : nb_whsec_xxxxxxxxxxxxxxxxx
 *
 *   Bu bilgileri e-ticaret sitesinin .env dosyasına kopyalayın.
 */
class CreatePosClient extends Command
{
    protected $signature   = 'pos:create-client {name : E-ticaret sitesinin adı}';
    protected $description = 'Nova Banka Sanal POS için yeni bir API istemcisi oluşturur';

    public function handle(): int
    {
        $name = $this->argument('name');

        // Güvenli random anahtarlar üret
        $apiKey        = 'nb_key_'    . Str::random(24);
        $apiSecret     = 'nb_secret_' . Str::random(32);
        $webhookSecret = 'nb_whsec_'  . Str::random(32);

        $client = PosApiClient::create([
            'name'           => $name,
            'api_key'        => $apiKey,
            'api_secret'     => $apiSecret,
            'webhook_secret' => $webhookSecret,
            'is_active'      => true,
        ]);

        $this->newLine();
        $this->line('<fg=green>✓ İstemci oluşturuldu:</> ' . $name);
        $this->newLine();
        $this->table(
            ['Alan', 'Değer'],
            [
                ['API Key',         $apiKey],
                ['API Secret',      $apiSecret],
                ['Webhook Secret',  $webhookSecret],
                ['İstemci ID',      $client->id],
            ]
        );
        $this->newLine();
        $this->line('<fg=yellow>E-ticaret .env dosyasına kopyalayın:</>');
        $this->line("NOVA_BANKA_API_URL=http://localhost/novabanka/public/api/v1/pos");
        $this->line("NOVA_BANKA_API_KEY={$apiKey}");
        $this->line("NOVA_BANKA_API_SECRET={$apiSecret}");
        $this->line("NOVA_BANKA_WEBHOOK_SECRET={$webhookSecret}");
        $this->newLine();

        return self::SUCCESS;
    }
}
