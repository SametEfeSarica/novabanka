<?php
namespace App\Services;

use App\Models\Card;
use App\Models\User;
use App\Models\Account;

class CardService
{
    public function createVirtualCard(User $user, Account $account): array
    {
        $existingCard = Card::where('user_id', $user->id)
                           ->where('is_active', true)
                           ->count();

        if ($existingCard >= 5) {
            return ['success' => false, 'message' => 'Maksimum 5 kart oluşturabilirsiniz.'];
        }

        $card = Card::create([
            'user_id'          => $user->id,
            'account_id'       => $account->id,
            'card_number'      => $this->generateCardNumber(),
            'card_holder_name' => strtoupper($user->full_name),
            'expiry_month'     => date('m'),
            'expiry_year'      => date('Y') + 3,
            'cvv'              => str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT),
            'card_type'        => 'virtual',
            'card_brand'       => 'visa',
            'spending_limit'   => 10000.00,
            'online_shopping'  => true,
            'contactless'      => false,
        ]);

        return [
            'success' => true,
            'message' => 'Sanal kart başarıyla oluşturuldu.',
            'card'    => $card,
        ];
    }

    public function toggleFreezeCard(Card $card): array
    {
        $card->update(['is_frozen' => !$card->is_frozen]);
        $status = $card->is_frozen ? 'donduruldu' : 'aktifleştirildi';
        return ['success' => true, 'message' => "Kart $status.", 'is_frozen' => $card->is_frozen];
    }

    public function cancelCard(Card $card): array
    {
        $card->update(['is_active' => false]);
        return ['success' => true, 'message' => 'Kart iptal edildi.'];
    }

    public function updateSpendingLimit(Card $card, float $limit): array
    {
        if ($limit < 0 || $limit > 100000) {
            return ['success' => false, 'message' => 'Geçersiz limit. 0-100.000 TRY arasında olmalı.'];
        }
        $card->update(['spending_limit' => $limit]);
        return ['success' => true, 'message' => 'Harcama limiti güncellendi.'];
    }

    private function generateCardNumber(): string
    {
        do {
            $number = '4' . implode('', array_map(fn() => random_int(0, 9), range(1, 15)));
        } while (Card::where('card_number', $number)->exists());

        return $number;
    }
}