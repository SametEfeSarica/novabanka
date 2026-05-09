<?php

use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\PosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Genel Ödeme İşlemleri
Route::prefix('payment')->group(function () {
    Route::post('/card',    [PaymentApiController::class, 'processPayment']);
    Route::post('/balance', [PaymentApiController::class, 'checkBalance']);
});

// SANAL POS SİSTEMİ (İLAN SİTESİNİN BAĞLANDIĞI YER)
// ÖNEMLİ: Dışarıdan erişim için middleware('pos.auth') kaldırıldı.
Route::prefix('v1/pos')->group(function () {

    // Yeni ödeme oturumu oluşturma (İlan sitesi buraya POST atar)
    Route::post('/create-session', [PosController::class, 'createSession'])
        ->name('pos.createSession');

    // Ödeme durumunu sorgulama (İlan sitesi buraya GET atar)
    Route::get('/session/{token}/status', [PosController::class, 'getSessionStatus'])
        ->name('pos.sessionStatus');
});
