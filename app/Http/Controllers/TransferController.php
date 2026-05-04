<?php
namespace App\Http\Controllers;

use App\Services\TransferService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(private TransferService $transferService) {}

    public function index()
    {
        $user     = auth()->user();
        $accounts = $user->accounts;
        return view('transfer.index', compact('accounts'));
    }

    public function send(Request $request)
    {
        
        $temizIban = strtoupper(str_replace(' ', '', $request->receiver_iban));
        $request->merge(['receiver_iban' => $temizIban]);

        
        $request->validate([
            'account_id'  => 'required|exists:accounts,id',
            'receiver_iban'=> 'required|string|size:26',
            'amount'      => 'required|numeric|min:1|max:50000',
            'description' => 'nullable|string|max:100',
        ], [
            'receiver_iban.size' => 'IBAN 26 karakter olmalıdır. (Eksik veya fazla girdiniz)',
            'amount.min'        => 'Minimum transfer tutarı 1 TRY\'dir.',
            'amount.max'        => 'Günlük maximum limit 50.000 TRY\'dir.',
        ]);

        $account = auth()->user()->accounts()->findOrFail($request->account_id);

        $result = $this->transferService->transferByIban(
            $account,
            $request->receiver_iban,
            $request->amount,
            $request->description
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function lookupIban(Request $request)
    {
        $iban    = strtoupper(str_replace(' ', '', $request->iban));
        $account = \App\Models\Account::where('iban', $iban)->with('user')->first();

        if ($account) {
            return response()->json([
                'found'      => true,
                'name'       => $account->user->full_name,
                'bank'       => 'NovaBanka',
            ]);
        }

        return response()->json(['found' => false]);
    }
}