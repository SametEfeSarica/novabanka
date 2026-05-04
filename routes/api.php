<?php
use App\Http\Controllers\Api\PaymentApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('payment')->group(function () {
    Route::post('/card',    [PaymentApiController::class, 'processPayment']);
    Route::post('/balance', [PaymentApiController::class, 'checkBalance']);
});