<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Ayarlar sayfasını göster
     */
    public function index()
    {
        $user = auth()->user()->load('cards');
        return view('settings.index', compact('user'));
    }

    /**
     * Kişisel bilgileri güncelle
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'surname'    => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
            'phone'      => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ], [
            'name.required'    => 'Ad alanı zorunludur.',
            'surname.required' => 'Soyad alanı zorunludur.',
            'email.required'   => 'E-posta alanı zorunludur.',
            'email.email'      => 'Geçerli bir e-posta adresi girin.',
            'email.unique'     => 'Bu e-posta adresi zaten kullanılıyor.',
            'birth_date.before'=> 'Doğum tarihi bugünden önce olmalıdır.',
        ]);

        auth()->user()->update([
            'name'       => $request->name,
            'surname'    => $request->surname,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'birth_date' => $request->birth_date,
        ]);

        return back()->with('success', 'Kişisel bilgileriniz başarıyla güncellendi.');
    }

    /**
     * Şifre değiştir
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers(),
            ],
        ], [
            'current_password.required' => 'Mevcut şifrenizi girin.',
            'password.required'         => 'Yeni şifre zorunludur.',
            'password.confirmed'        => 'Şifreler eşleşmiyor.',
            'password.min'              => 'Şifre en az 8 karakter olmalıdır.',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()
                ->withErrors(['current_password' => 'Mevcut şifreniz hatalı.'])
                ->withInput();
        }

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        // Şifre değişince oturumu sonlandır
        auth()->logoutOtherDevices($request->password);

        return back()->with('success', 'Şifreniz başarıyla güncellendi. Diğer cihazlardaki oturumlar sonlandırıldı.');
    }

    /**
     * Kart özelliklerini güncelle (online shopping, contactless)
     */
    public function updateCardFeatures(Request $request, Card $card)
    {
        // Yetki kontrolü
        if ($card->user_id !== auth()->id()) {
            abort(403, 'Bu karta erişim yetkiniz yok.');
        }

        $card->update([
            'online_shopping' => $request->boolean('online_shopping'),
            'contactless'     => $request->boolean('contactless'),
        ]);

        return back()->with('success', 'Kart ayarları güncellendi.');
    }

    /**
     * Güvenlik ayarları güncelle
     */
    public function updateSecurity(Request $request)
    {
        // Gerçek projede: two_factor_enabled, sms_notification vb. user tablosunda saklanır
        // Şimdilik sadece bildirim göster
        return back()->with('success', 'Güvenlik ayarları güncellendi.');
    }

    /**
     * Bildirim tercihlerini güncelle
     */
    public function updateNotifications(Request $request)
    {
        // Gerçek projede: kullanıcıya ait bir notification_preferences JSON alanı
        // veya ayrı bir tablo kullanılır. Şimdilik placeholder.
        return back()->with('success', 'Bildirim tercihleriniz kaydedildi.');
    }
}
