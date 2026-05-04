<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ExchangeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/',         [AuthController::class, 'showLogin'])->name('home');
    Route::get('/giris',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/giris',   [AuthController::class, 'login']);
    Route::get('/kayit',    [AuthController::class, 'showRegister'])->name('register');
    Route::post('/kayit',   [AuthController::class, 'register']);
});

// Dikkat: Burada az önce oluşturduğumuz BankAuthMiddleware'i de ekleyebiliriz ama 
// şimdilik varsayılan auth'u kullanacağız.
Route::middleware(['auth'])->group(function () {
    Route::get('/panel', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/cikis', [AuthController::class, 'logout'])->name('logout');

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