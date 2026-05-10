<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransferService;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    public function __construct(private TransferService $transferService) {}

    public function processPayment(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('app.payment_api_key', 'novabanka_demo_key_2024')) {
            return response()->json(['success' => false, 'message' => 'Geçersiz API anahtarı.'], 401);
        }

        $request->validate([
            'card_number'  => 'required|string|size:16',
            'expiry_month' => 'required|string|size:2',
            'expiry_year'  => 'required|string|size:4',
            'cvv'          => 'required|string|size:3',
            'amount'       => 'required|numeric|min:1',
            'merchant_name'=> 'nullable|string|max:100',
        ]);

        $result = $this->transferService->processCardPayment(
            $request->card_number,
            $request->expiry_month,
            $request->expiry_year,
            $request->cvv,
            $request->amount,
            $request->merchant_name
        );

        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    public function checkBalance(Request $request)
    {
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('app.payment_api_key', 'novabanka_demo_key_2024')) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz erişim.'], 401);
        }

        $request->validate([
            'card_number' => 'required|string|size:16',
            'cvv'         => 'required|string|size:3',
        ]);

        $card = \App\Models\Card::where('card_number', $request->card_number)
                                ->where('cvv', $request->cvv)
                                ->with('account')
                                ->first();

        if (!$card || !$card->is_active) {
            return response()->json(['success' => false, 'message' => 'Kart bulunamadı veya aktif değil.']);
        }

        return response()->json([
            'success'         => true,
            'balance'         => $card->account->balance,
            'currency'        => 'TRY',
            'card_holder'     => $card->card_holder_name,
            'masked_number'   => $card->masked_number,
        ]);
    }
}
