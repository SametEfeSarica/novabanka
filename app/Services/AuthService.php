<?php
namespace App\Services;

use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name'       => $data['name'],
            'surname'    => $data['surname'],
            'email'      => $data['email'],
            'phone'      => $data['phone'],
            'tc_no'      => $data['tc_no'],
            'birth_date' => $data['birth_date'],
            'password'   => Hash::make($data['password']),
        ]);

        $this->createDefaultAccount($user, 'TRY');
        
        $account = $user->accounts()->first();
        $account->deposit(1000.00);

        return $user;
    }

    public function login(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt([
            'email'    => $email,
            'password' => $password,
            'is_active'=> true,
        ], $remember);
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    private function createDefaultAccount(User $user, string $currency): Account
    {
        return Account::create([
            'user_id'        => $user->id,
            'iban'           => $this->generateIban(),
            'account_number' => $this->generateAccountNumber(),
            'currency'       => $currency,
            'account_type'   => 'checking',
            'balance'        => 0.00,
        ]);
    }

    private function generateIban(): string
    {
        do {
            $ibanNum = '';
            for ($i = 0; $i < 24; $i++) {
                $ibanNum .= random_int(0, 9);
            }
            $iban = 'TR' . $ibanNum;
        } while (Account::where('iban', $iban)->exists());

        return $iban;
    }

    private function generateAccountNumber(): string
    {
        do {
            $number = '';
            for ($i = 0; $i < 16; $i++) {
                $number .= random_int(0, 9);
            }
        } while (Account::where('account_number', $number)->exists());

        return $number;
    }
}