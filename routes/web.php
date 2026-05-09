<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ExchangeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// ─── SAMET İÇİN SÜPER GÜÇLÜ KURULUM VE ANAHTAR OLUŞTURUCU ───

// 1. Veritabanı Tablolarını Zorla Oluşturur
Route::get('/banka-kur', function() {
    try {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        DB::statement("CREATE TABLE IF NOT EXISTS pos_api_clients (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255),
            api_key VARCHAR(255) UNIQUE,
            api_secret VARCHAR(255),
            webhook_secret VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        DB::statement("CREATE TABLE IF NOT EXISTS pos_sessions (
            id SERIAL PRIMARY KEY,
            client_id INTEGER,
            token VARCHAR(255) UNIQUE,
            order_id VARCHAR(255),
            amount DECIMAL(15,2),
            currency VARCHAR(3) DEFAULT 'TRY',
            status VARCHAR(20) DEFAULT 'pending',
            customer_name VARCHAR(255),
            customer_email VARCHAR(255),
            return_url TEXT,
            webhook_url TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $mesaj = "Veritabanı SQL ile kontrol edildi: <br>";
        $tablolar = ['pos_api_clients', 'pos_sessions', 'users'];
        foreach ($tablolar as $tablo) {
            $mesaj .= Schema::hasTable($tablo) ? "✅ {$tablo} hazır. <br>" : "❌ {$tablo} eksik! <br>";
        }

        return "<h1>Süper Güçlü Kurulum Tamam!</h1>" . $mesaj;
    } catch (\Exception $e) {
        return "<h1>SQL Hatası:</h1> " . $e->getMessage();
    }
});

// 2. Terminale Gerek Kalmadan API Anahtarı Üretir
Route::get('/client-olustur', function() {
    try {
        // Tabloya yeni bir istemci (Ensar) ekliyoruz
        $apiKey = Str::random(32);
        $apiSecret = Str::random(64);

        DB::table('pos_api_clients')->insert([
            'name' => 'Ensar Ilan Sitesi',
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'webhook_secret' => Str::random(64),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return "<h1>Müşteri Kaydı Başarılı!</h1>" .
               "<b>Bu bilgileri Ensar'a gönder:</b><br><br>" .
               "API KEY: <code>$apiKey</code><br>" .
               "API SECRET: <code>$apiSecret</code><br><br>" .
               "<i>Not: Sayfayı yenilersen yeni bir tane daha oluşturur!</i>";
    } catch (\Exception $e) {
        return "<h1>Hata:</h1> " . $e->getMessage();
    }
});
// ───────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/',          [AuthController::class, 'showLogin'])->name('home');
    Route::get('/giris',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/giris',    [AuthController::class, 'login']);
    Route::get('/kayit',     [AuthController::class, 'showRegister'])->name('register');
    Route::post('/kayit',    [AuthController::class, 'register']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/panel', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/cikis', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('hareketler')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
    });

    Route::prefix('ayarlar')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::patch('/profil',           [SettingsController::class, 'updateProfile'])->name('updateProfile');
        Route::patch('/sifre',            [SettingsController::class, 'updatePassword'])->name('updatePassword');
        Route::patch('/kart/{card}/ozellikler', [SettingsController::class, 'updateCardFeatures'])->name('updateCardFeatures');
        Route::patch('/guvenlik',         [SettingsController::class, 'updateSecurity'])->name('updateSecurity');
        Route::patch('/bildirimler',      [SettingsController::class, 'updateNotifications'])->name('updateNotifications');
    });

    Route::prefix('transfer')->name('transfer.')->group(function () {
        Route::get('/',              [TransferController::class, 'index'])->name('index');
        Route::post('/gonder',       [TransferController::class, 'send'])->name('send');
        Route::get('/iban-sorgula',  [TransferController::class, 'lookupIban'])->name('lookup');
    });

    Route::prefix('kartlar')->name('cards.')->group(function () {
        Route::get('/',                      [CardController::class, 'index'])->name('index');
        Route::post('/olustur',              [CardController::class, 'create'])->name('create');
        Route::post('/{card}/dondur',        [CardController::class, 'toggleFreeze'])->name('freeze');
        Route::post('/{card}/iptal',         [CardController::class, 'cancel'])->name('cancel');
        Route::post('/{card}/limit-guncelle',[CardController::class, 'updateLimit'])->name('limit');
    });

    Route::prefix('borsa')->name('exchange.')->group(function () {
        Route::get('/',              [ExchangeController::class, 'index'])->name('index');
        Route::get('/fiyatlar',      [ExchangeController::class, 'prices'])->name('prices');
        Route::post('/al',           [ExchangeController::class, 'buy'])->name('buy');
        Route::post('/sat/{investment}', [ExchangeController::class, 'sell'])->name('sell');
    });
});

Route::get('/checkout/{token}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{token}/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/result/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/result/failed', [CheckoutController::class, 'failed'])->name('checkout.failed');
