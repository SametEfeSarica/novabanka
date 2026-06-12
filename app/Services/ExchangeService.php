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
        // Anlık güncellemeleri ekranda görebilmek için cache süresini 30 saniyeye düşürdük
        return Cache::remember('live_prices', 30, function () {
            $prices = [
                // Kripto Paralar
                'BTC'   => ['name' => 'Bitcoin',    'price' => 0, 'change' => 0, 'icon' => 'fa-brands fa-bitcoin'],
                'ETH'   => ['name' => 'Ethereum',   'price' => 0, 'change' => 0, 'icon' => 'fa-brands fa-ethereum'],
                'SOL'   => ['name' => 'Solana',     'price' => 0, 'change' => 0, 'icon' => 'fa-brands fa-stripe-s'],
                'XRP'   => ['name' => 'Ripple',     'price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-xmarks-lines'],
                'AVAX'  => ['name' => 'Avalanche',  'price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-mountain'],
                
                // Döviz ve Emtia
                'USD'   => ['name' => 'Dolar',      'price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-dollar-sign'],
                'EUR'   => ['name' => 'Euro',       'price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-euro-sign'],
                'GBP'   => ['name' => 'Sterlin',    'price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-sterling-sign'],
                'CHF'   => ['name' => 'İsviçre Fr.','price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-money-bill-wave'],
                'JPY'   => ['name' => 'Japon Yeni', 'price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-yen-sign'],
                'GOLD'  => ['name' => 'Altın (gr)', 'price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-coins'],

                // Şirket Hisseleri
                'AAPL'  => ['name' => 'Apple Inc.', 'price' => 0, 'change' => 0, 'icon' => 'fa-brands fa-apple'],
                'TSLA'  => ['name' => 'Tesla',      'price' => 0, 'change' => 0, 'icon' => 'fa-solid fa-car-side'],
                'GOOGL' => ['name' => 'Google',     'price' => 0, 'change' => 0, 'icon' => 'fa-brands fa-google'],
            ];

            try {
                // Kripto: CoinGecko (Yeni coinler eklendi)
                $cryptoResponse = Http::timeout(5)->get(
                    'https://api.coingecko.com/api/v3/simple/price',
                    [
                        'ids'                 => 'bitcoin,ethereum,solana,ripple,avalanche-2',
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
                    $prices['SOL']['price']  = $data['solana']['try'] ?? 0;
                    $prices['SOL']['change'] = round($data['solana']['try_24h_change'] ?? 0, 2);
                    $prices['XRP']['price']  = $data['ripple']['try'] ?? 0;
                    $prices['XRP']['change'] = round($data['ripple']['try_24h_change'] ?? 0, 2);
                    $prices['AVAX']['price'] = $data['avalanche-2']['try'] ?? 0;
                    $prices['AVAX']['change']= round($data['avalanche-2']['try_24h_change'] ?? 0, 2);
                }

                // Döviz: Frankfurter API (Yeni kurlar eklendi)
                $today     = now()->format('Y-m-d');
                $yesterday = now()->subDay()->format('Y-m-d');
                $currencies = 'USD,EUR,GBP,CHF,JPY';

                $todayFx = Http::timeout(5)->get("https://api.frankfurter.app/{$today}", ['from' => 'TRY', 'to' => $currencies]);
                $yesterdayFx = Http::timeout(5)->get("https://api.frankfurter.app/{$yesterday}", ['from' => 'TRY', 'to' => $currencies]);

                if ($todayFx->successful()) {
                    $todayRates = $todayFx->json()['rates'] ?? [];
                    foreach (explode(',', $currencies) as $sym) {
                        if (!empty($todayRates[$sym]) && $todayRates[$sym] > 0) {
                            // JPY fiyatını düzgün göstermek için özel hesaplama
                            $multiplier = $sym === 'JPY' ? 100 : 1; 
                            $prices[$sym]['price'] = round(($multiplier / $todayRates[$sym]), 4);
                        }
                    }

                    if ($yesterdayFx->successful()) {
                        $yestRates = $yesterdayFx->json()['rates'] ?? [];
                        foreach (explode(',', $currencies) as $sym) {
                            $todayPrice = $prices[$sym]['price'];
                            $yestRate   = $yestRates[$sym] ?? 0;

                            if ($yestRate > 0 && $todayPrice > 0) {
                                $multiplier = $sym === 'JPY' ? 100 : 1;
                                $yestPrice = round(($multiplier / $yestRate), 4);
                                if ($yestPrice > 0) {
                                    $prices[$sym]['change'] = round((($todayPrice - $yestPrice) / $yestPrice) * 100, 2);
                                }
                            }
                        }
                    }
                }

                // Hisse Senetleri & Altın (Projenin sunumunda apinin çökmemesi için dinamik simülasyon)
                $prices['GOLD']['price']  = 2450.50 + (rand(-10, 10) / 10);
                $prices['GOLD']['change'] = 0.45 + (rand(-5, 5) / 100);
                
                $prices['AAPL']['price']  = 5820.30 + (rand(-50, 50) / 10);
                $prices['AAPL']['change'] = 1.20 + (rand(-10, 10) / 100);
                
                $prices['TSLA']['price']  = 5400.10 + (rand(-80, 80) / 10);
                $prices['TSLA']['change'] = -0.80 + (rand(-15, 15) / 100);
                
                $prices['GOOGL']['price'] = 5600.00 + (rand(-40, 40) / 10);
                $prices['GOOGL']['change']= 0.90 + (rand(-10, 10) / 100);

            } catch (\Exception $e) {
                // Herhangi bir api kopmasında projenin patlamaması için Fallback
                $prices['BTC']['price']  = 2150000;
                $prices['ETH']['price']  = 95000;
                $prices['USD']['price']  = 32.50;
            }

            return $prices;
        });
    }

    public function buy(Account $account, string $symbol, float $amountInTRY): array
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

                // Varlık tipini belirleme
                $type = 'currency';
                if (in_array($symbol, ['BTC', 'ETH', 'SOL', 'XRP', 'AVAX'])) $type = 'crypto';
                if (in_array($symbol, ['AAPL', 'TSLA', 'GOOGL'])) $type = 'stock';
                if ($symbol === 'GOLD') $type = 'commodity';

                $investment->type           = $type;
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

    public function sell(Investment $investment, float $quantity): array
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

                // Matematiksel kesinlik için 8 ondalık haneye yuvarlama işlemi eklendi
                $investment->quantity = round($investment->quantity - $quantity, 8);
                
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
