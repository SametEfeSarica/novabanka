use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\PosController;
use Illuminate\Support\Facades\Route;

Route::prefix('payment')->group(function () {
    Route::post('/card',    [PaymentApiController::class, 'processPayment']);
    Route::post('/balance', [PaymentApiController::class, 'checkBalance']);
});

Route::prefix('v1/pos')->middleware('pos.auth')->group(function () {

    Route::post('/create-session', [PosController::class, 'createSession'])
        ->name('pos.createSession');

    Route::get('/session/{token}/status', [PosController::class, 'getSessionStatus'])
        ->name('pos.sessionStatus');
});