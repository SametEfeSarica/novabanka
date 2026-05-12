<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransferService
{
    const TRANSFER_FEE_PERCENT = 0;
    const MAX_DAILY_TRANSFER   = 10000000;

    public function transferByIban(
        Account $senderAccount,
        string $receiverIban,
        float $amount,
        string $description = ''
    ): array
    {
        $receiverAccount = Account::where('iban', $receiverIban)->first();

        if (!$receiverAccount) {
            return ['success' => false, 'message' => 'IBAN bulunamadı. Lütfen kontrol edin.'];
        }

        if ($senderAccount->id === $receiverAccount->id) {
            return ['success' => false, 'message' => 'Kendi hesabınıza transfer yapamazsınız.'];
        }

        if (!$senderAccount->hasSufficientBalance($amount)) {
            return ['success' => false, 'message' => 'Yetersiz bakiye.'];
        }

        if ($amount < 1) {
            return ['success' => false, 'message' => 'Minimum transfer tutarı 1 TRY\'dir.'];
        }

        try {
            DB::transaction(function () use ($senderAccount, $receiverAccount, $amount, $description) {
                $senderAccount->withdraw($amount);
                $receiverAccount->deposit($amount);

                Transaction::create([
                    'sender_account_id'   => $senderAccount->id,
                    'receiver_account_id' => $receiverAccount->id,
                    'type'                => Transaction::TYPE_TRANSFER,
                    'amount'              => $amount,
                    'currency'            => $senderAccount->currency,
                    'description'         => $description ?: 'IBAN Transferi',
                    'reference_no'        => Transaction::generateReferenceNo(),
                    'status'              => 'completed',
                ]);
            });

            return [
                'success'  => true,
                'message'  => number_format($amount, 2) . ' TRY başarıyla gönderildi.',
                'receiver' => $receiverAccount->user->full_name,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'İşlem sırasında hata oluştu. Lütfen tekrar deneyin.'];
        }
    }

    public function processCardPayment(
        string $cardNumber,
        string $expiryMonth,
        string $expiryYear,
        string $cvv,
        float $amount,
        string $merchantName = ''
    ): array
    {
        $card = \App\Models\Card::where('card_number', $cardNumber)->first();

        if (!$card) {
            return ['success' => false, 'message' => 'Kart bulunamadı.'];
        }

        if ($card->cvv !== $cvv ||
            $card->expiry_month !== $expiryMonth ||
            $card->expiry_year !== $expiryYear) {
            return ['success' => false, 'message' => 'Kart bilgileri hatalı.'];
        }

        if (!$card->is_active || $card->is_frozen) {
            return ['success' => false, 'message' => 'Kart aktif değil veya dondurulmuş.'];
        }

        $account = $card->account;
        if (!$account->hasSufficientBalance($amount)) {
            return ['success' => false, 'message' => 'Yetersiz bakiye.'];
        }

        try {
            DB::transaction(function () use ($account, $card, $amount, $merchantName) {
                $account->withdraw($amount);

                Transaction::create([
                    'sender_account_id' => $account->id,
                    'type'              => Transaction::TYPE_PAYMENT,
                    'amount'            => $amount,
                    'currency'          => $account->currency,
                    'description'       => 'Alışveriş: ' . ($merchantName ?: 'Online Ödeme'),
                    'reference_no'      => Transaction::generateReferenceNo(),
                    'status'            => 'completed',
                    'metadata'          => ['merchant' => $merchantName, 'card_last4' => substr($card->card_number, -4)],
                ]);
            });

            return [
                'success'      => true,
                'message'      => 'Ödeme başarılı.',
                'reference_no' => Transaction::generateReferenceNo(),
                'new_balance'  => $account->fresh()->balance,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ödeme işlemi başarısız.'];
        }
    }

    public function getTransactionHistory(Account $account, int $limit = 20): \Illuminate\Support\Collection
    {
        $sent     = $account->sentTransactions()->latest()->take($limit)->get();
        $received = $account->receivedTransactions()->latest()->take($limit)->get();

        return $sent->merge($received)->sortByDesc('created_at')->take($limit);
    }
}
