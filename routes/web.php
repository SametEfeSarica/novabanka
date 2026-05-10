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
use Illuminate\Support\Facades\DB;


Route::middleware('guest')->group(function () {
    Route::get('/',       [AuthController::class, 'showLogin'])->name('home');
    Route::get('/giris',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/giris', [AuthController::class, 'login']);
    Route::get('/kayit',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/kayit', [AuthController::class, 'register']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/panel',  [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/cikis', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('hareketler')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
    });

    Route::prefix('ayarlar')->name('settings.')->group(function () {
        Route::get('/',                             [SettingsController::class, 'index'])->name('index');
        Route::patch('/profil',                     [SettingsController::class, 'updateProfile'])->name('updateProfile');
        Route::patch('/sifre',                      [SettingsController::class, 'updatePassword'])->name('updatePassword');
        Route::patch('/kart/{card}/ozellikler',     [SettingsController::class, 'updateCardFeatures'])->name('updateCardFeatures');
        Route::patch('/guvenlik',                   [SettingsController::class, 'updateSecurity'])->name('updateSecurity');
        Route::patch('/bildirimler',                [SettingsController::class, 'updateNotifications'])->name('updateNotifications');
    });

    Route::prefix('transfer')->name('transfer.')->group(function () {
        Route::get('/',             [TransferController::class, 'index'])->name('index');
        Route::post('/gonder',      [TransferController::class, 'send'])->name('send');
        Route::get('/iban-sorgula', [TransferController::class, 'lookupIban'])->name('lookup');
    });

    Route::prefix('kartlar')->name('cards.')->group(function () {
        Route::get('/',                       [CardController::class, 'index'])->name('index');
        Route::post('/olustur',               [CardController::class, 'create'])->name('create');
        Route::post('/{card}/dondur',         [CardController::class, 'toggleFreeze'])->name('freeze');
        Route::post('/{card}/iptal',          [CardController::class, 'cancel'])->name('cancel');
        Route::post('/{card}/limit-guncelle', [CardController::class, 'updateLimit'])->name('limit');
    });

    Route::prefix('borsa')->name('exchange.')->group(function () {
        Route::get('/',                  [ExchangeController::class, 'index'])->name('index');
        Route::get('/fiyatlar',          [ExchangeController::class, 'prices'])->name('prices');
        Route::post('/al',               [ExchangeController::class, 'buy'])->name('buy');
        Route::post('/sat/{investment}', [ExchangeController::class, 'sell'])->name('sell');
    });
});

Route::get('/checkout/{token}',          [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{token}/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/result/success',   [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/result/failed',    [CheckoutController::class, 'failed'])->name('checkout.failed');
