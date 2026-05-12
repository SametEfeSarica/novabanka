<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExchangeService
{
    public function getLivePrices(): array
    {
        return Cache::remember('live_prices', 300, function () {
            $prices = [
                'BTC'  => ['name' => 'Bitcoin',    'price' => 0, 'change' => 0, 'icon' => '₿'],
                'ETH'  => ['name' => 'Ethereum',   'price' => 0, 'change' => 0, 'icon' => 'Ξ'],
                'USD'  => ['name' => 'Dolar',      'price' => 0, 'change' => 0, 'icon' => '$'],
                'EUR'  => ['name' => 'Euro',       'price' => 0, 'change' => 0, 'icon' => '€'],
                'GBP'  => ['name' => 'Sterlin',    'price' => 0, 'change' => 0, 'icon' => '£'],
                'GOLD' => ['name' => 'Altın (gr)', 'price' => 0, 'change' => 0, 'icon' => '🥇'],
            ];

            try {
                // Kripto: CoinGecko (24s değişim dahil)
                $cryptoResponse = Http::timeout(5)->get(
                    'https://api.coingecko.com/api/v3/simple/price',
                    [
                        'ids'                 => 'bitcoin,ethereum',
                        'vs_currencies'       => 'try',
                        'include_24hr_change' => 'true',
                    ]
                );

                if ($cryptoResponse->successful()) {
                    $data = $cryptoResponse->json();
                    $prices['BTC']['price']  = $data['bitcoin']['try'] ?? 0;
                    $prices['BTC']['change'] = round($data['bitcoin']['try_24h_change'] ?? 0, 2);
                    $prices['ETH']['price']  = $data['ethereum']['try'] ?? 0;
                    $prices['ETH']['change'] = round($data['ethereum']['try_24h_change'] ?? 0, 2);
                }

                // Döviz: Frankfurter API (ECB, ücretsiz)
                // Bugün ve dün fiyatını çekip 24s değişim hesaplıyoruz
                $today     = now()->format('Y-m-d');
                $yesterday = now()->subDay()->format('Y-m-d');

                $todayFx = Http::timeout(5)->get(
                    "https://api.frankfurter.app/{$today}",
                    ['from' => 'TRY', 'to' => 'USD,EUR,GBP']
                );

                $yesterdayFx = Http::timeout(5)->get(
                    "https://api.frankfurter.app/{$yesterday}",
                    ['from' => 'TRY', 'to' => 'USD,EUR,GBP']
                );

                if ($todayFx->successful()) {
                    $todayRates = $todayFx->json()['rates'] ?? [];

                    foreach (['USD', 'EUR', 'GBP'] as $sym) {
                        if (!empty($todayRates[$sym]) && $todayRates[$sym] > 0) {
                            $prices[$sym]['price'] = round(1 / $todayRates[$sym], 4);
                        }
                    }

                    // Dünkü veri de geldiyse değişim yüzdesini hesapla
                    if ($yesterdayFx->successful()) {
                        $yestRates = $yesterdayFx->json()['rates'] ?? [];

                        foreach (['USD', 'EUR', 'GBP'] as $sym) {
                            $todayPrice = $prices[$sym]['price'];
                            $yestRate   = $yestRates[$sym] ?? 0;

                            if ($yestRate > 0 && $todayPrice > 0) {
                                $yestPrice = round(1 / $yestRate, 4);
                                if ($yestPrice > 0) {
                                    $prices[$sym]['change'] = round(
                                        (($todayPrice - $yestPrice) / $yestPrice) * 100, 2
                                    );
                                }
                            }
                        }
                    }
                }

            } catch (\Exception $e) {
                // API'ye ulaşılamazsa sabit fallback değerleri
                $prices['BTC']['price']  = 2850000;
                $prices['ETH']['price']  = 95000;
                $prices['USD']['price']  = 32.50;
                $prices['EUR']['price']  = 35.20;
                $prices['GBP']['price']  = 41.30;
                $prices['GOLD']['price'] = 2100;
            }

            return $prices;
        });
    }

    public function buy(
        Account $account,
        string $symbol,
        float $amountInTRY
    ): array
    {
        $prices = $this->getLivePrices();

        if (!isset($prices[$symbol])) {
            return ['success' => false, 'message' => 'Geçersiz sembol.'];
        }

        $currentPrice = $prices[$symbol]['price'];

        if ($currentPrice <= 0) {
            return ['success' => false, 'message' => 'Fiyat bilgisi alınamadı.'];
        }

        if (!$account->hasSufficientBalance($amountInTRY)) {
            return ['success' => false, 'message' => 'Yetersiz bakiye.'];
        }

        if ($amountInTRY < 50) {
            return ['success' => false, 'message' => 'Minimum alım tutarı 50 TRY\'dir.'];
        }

        $quantity = $amountInTRY / $currentPrice;

        try {
            DB::transaction(function () use ($account, $symbol, $quantity, $amountInTRY, $currentPrice, $prices) {
                $account->withdraw($amountInTRY);

                $investment = Investment::firstOrNew([
                    'user_id'    => $account->user_id,
                    'account_id' => $account->id,
                    'symbol'     => $symbol,
                ]);

                $investment->type           = in_array($symbol, ['BTC', 'ETH']) ? 'crypto' : 'currency';
                $investment->quantity       = ($investment->quantity ?? 0) + $quantity;
                $investment->buy_price      = $currentPrice;
                $investment->current_price  = $currentPrice;
                $investment->total_invested = ($investment->total_invested ?? 0) + $amountInTRY;
                $investment->save();

                Transaction::create([
                    'sender_account_id' => $account->id,
                    'type'              => Transaction::TYPE_EXCHANGE,
                    'amount'            => $amountInTRY,
                    'currency'          => 'TRY',
                    'description'       => $symbol . ' Alımı - ' . number_format($quantity, 8) . ' adet',
                    'reference_no'      => Transaction::generateReferenceNo(),
                    'status'            => 'completed',
                    'metadata'          => ['symbol' => $symbol, 'quantity' => $quantity, 'price' => $currentPrice],
                ]);
            });

            return [
                'success'  => true,
                'message'  => number_format($quantity, 6) . ' ' . $symbol . ' başarıyla satın alındı.',
                'quantity' => $quantity,
                'price'    => $currentPrice,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'İşlem başarısız oldu.'];
        }
    }

    public function sell(
        Investment $investment,
        float $quantity
    ): array
    {
        if ($quantity > $investment->quantity) {
            return ['success' => false, 'message' => 'Yeterli varlık yok.'];
        }

        $prices        = $this->getLivePrices();
        $currentPrice  = $prices[$investment->symbol]['price'] ?? 0;
        $amountInTRY   = $quantity * $currentPrice;

        try {
            DB::transaction(function () use ($investment, $quantity, $amountInTRY, $currentPrice) {
                $account = $investment->account;
                $account->deposit($amountInTRY);

                $investment->quantity -= $quantity;
                if ($investment->quantity <= 0) {
                    $investment->delete();
                } else {
                    $investment->save();
                }

                Transaction::create([
                    'receiver_account_id' => $account->id,
                    'type'                => Transaction::TYPE_EXCHANGE,
                    'amount'              => $amountInTRY,
                    'currency'            => 'TRY',
                    'description'         => $investment->symbol . ' Satışı - ' . number_format($quantity, 8) . ' adet',
                    'reference_no'        => Transaction::generateReferenceNo(),
                    'status'              => 'completed',
                    'metadata'            => ['symbol' => $investment->symbol, 'quantity' => $quantity, 'price' => $currentPrice],
                ]);
            });

            return [
                'success' => true,
                'message' => number_format($amountInTRY, 2) . ' TRY hesabınıza eklendi.',
                'amount'  => $amountInTRY,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Satış işlemi başarısız.'];
        }
    }
}
