<?php
namespace App\Http\Controllers;

use App\Models\Card;
use App\Services\CardService;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function __construct(private CardService $cardService) {}

    public function index()
    {
        $user     = auth()->user();
        $cards    = $user->cards()->with('account')->get();
        $accounts = $user->accounts;
        return view('cards.index', compact('cards', 'accounts'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
        ]);

        $account = auth()->user()->accounts()->findOrFail($request->account_id);
        $result  = $this->cardService->createVirtualCard(auth()->user(), $account);

        return response()->json($result);
    }

    public function toggleFreeze(Card $card)
    {
        // Yetkilendirme burada yapılmalı (gerçek bir projede Gate veya Policy kullanılır)
        if ($card->user_id !== auth()->id()) {
            abort(403);
        }
        return response()->json($this->cardService->toggleFreezeCard($card));
    }

    public function cancel(Card $card)
    {
        if ($card->user_id !== auth()->id()) {
             abort(403);
        }
        return response()->json($this->cardService->cancelCard($card));
    }

    public function updateLimit(Request $request, Card $card)
    {
        if ($card->user_id !== auth()->id()) {
             abort(403);
        }
        $request->validate(['limit' => 'required|numeric|min:0|max:100000']);
        return response()->json($this->cardService->updateSpendingLimit($card, $request->limit));
    }
}