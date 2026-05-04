<?php
namespace App\Http\Controllers;

use App\Services\ExchangeService;
use App\Services\TransferService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private ExchangeService $exchangeService,
        private TransferService $transferService
    ) {}

    public function index()
    {
        $user        = auth()->user();
        $accounts    = $user->accounts()->with('cards')->get();
        $investments = $user->investments;
        $prices      = $this->exchangeService->getLivePrices();

        $primaryAccount = $user->primaryAccount();
        $recentTx = $primaryAccount
            ? $this->transferService->getTransactionHistory($primaryAccount, 10)
            : collect();

        return view('dashboard', compact('user', 'accounts', 'investments', 'prices', 'recentTx'));
    }
}