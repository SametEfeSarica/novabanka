<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TransactionController extends Controller
{
    /**
     * Hesap hareketleri — liste + filtre + CSV export
     */
    public function index(Request $request)
    {
        $user    = auth()->user();
        $account = $user->primaryAccount();

        if (!$account) {
            return view('transactions.index', [
                'transactions' => collect()->paginate(20),
                'account'      => null,
                'summary'      => $this->emptySummary(),
            ]);
        }

        // ── Build query
        $query = Transaction::where(function ($q) use ($account) {
                $q->where('sender_account_id',   $account->id)
                  ->orWhere('receiver_account_id', $account->id);
            })
            ->with(['senderAccount.user', 'receiverAccount.user'])
            ->latest();

        // Tür filtresi
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Yön filtresi
        if ($request->direction === 'out') {
            $query->where('sender_account_id', $account->id);
        } elseif ($request->direction === 'in') {
            $query->where('receiver_account_id', $account->id);
        }

        // Tarih aralığı
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Arama (açıklama veya referans)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description',  'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        // ── CSV export
        if ($request->export === 'csv') {
            return $this->exportCsv($query->get(), $account);
        }

        // ── Summary istatistikleri (bu ay)
        $allTx = Transaction::where(function ($q) use ($account) {
                $q->where('sender_account_id',   $account->id)
                  ->orWhere('receiver_account_id', $account->id);
            })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();

        $incoming = $allTx->where('receiver_account_id', $account->id);
        $outgoing = $allTx->where('sender_account_id',   $account->id);

        $summary = [
            'total_in'    => $incoming->sum('amount'),
            'total_out'   => $outgoing->sum('amount'),
            'count_in'    => $incoming->count(),
            'count_out'   => $outgoing->count(),
            'total_count' => $allTx->count(),
            'date_range'  => now()->format('F Y'),
        ];

        $transactions = $query->paginate(20)->withQueryString();

        return view('transactions.index', compact('transactions', 'account', 'summary'));
    }

    /**
     * CSV dışa aktarma
     */
    private function exportCsv($transactions, $account): \Symfony\Component\HttpFoundation\Response
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="hesap_hareketleri_' . now()->format('Ymd') . '.csv"',
        ];

        $rows = [];
        $rows[] = ['Tarih', 'Tür', 'Açıklama', 'Referans No', 'Tutar', 'Yön', 'Durum', 'Para Birimi'];

        foreach ($transactions as $tx) {
            $isOut = $tx->sender_account_id === $account->id;
            $rows[] = [
                $tx->created_at->format('d.m.Y H:i'),
                $tx->getTypeLabel(),
                $tx->description,
                $tx->reference_no,
                number_format($tx->amount, 2, ',', '.'),
                $isOut ? 'Giden' : 'Gelen',
                $tx->status === 'completed' ? 'Tamamlandı' : ($tx->status === 'pending' ? 'Bekliyor' : 'Başarısız'),
                $tx->currency,
            ];
        }

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM (Excel uyumu için)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            foreach ($rows as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function emptySummary(): array
    {
        return [
            'total_in'    => 0,
            'total_out'   => 0,
            'count_in'    => 0,
            'count_out'   => 0,
            'total_count' => 0,
            'date_range'  => now()->format('F Y'),
        ];
    }
}
