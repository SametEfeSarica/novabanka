<?php
namespace App\Http\Controllers;

use App\Models\Investment;
use App\Services\ExchangeService;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function __construct(private ExchangeService $exchangeService) {}

    public function index()
    {
        $user        = auth()->user();
        $accounts    = $user->accounts;
        $investments = $user->investments;
        $prices      = $this->exchangeService->getLivePrices();

        return view('exchange.index', compact('accounts', 'investments', 'prices'));
    }

    public function prices()
    {
        return response()->json($this->exchangeService->getLivePrices());
    }

    public function buy(Request $request)
    {
        // Validation kurallarına yeni sembolleri ekliyoruz.
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'symbol'     => 'required|string|in:BTC,ETH,SOL,XRP,AVAX,USD,EUR,GBP,CHF,JPY,GOLD,AAPL,TSLA,GOOGL',
            'amount'     => 'required|numeric|min:50',
        ]);

        $account = auth()->user()->accounts()->findOrFail($request->account_id);
        $result  = $this->exchangeService->buy($account, $request->symbol, $request->amount);

        return response()->json($result);
    }

    public function sell(Request $request, Investment $investment)
    {
        if ($investment->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }

        $request->validate(['quantity' => 'required|numeric|min:0.000001']);
        $result = $this->exchangeService->sell($investment, $request->quantity);

        return response()->json($result);
    }
}
