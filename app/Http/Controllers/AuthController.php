<?php
namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLogin()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required'    => 'E-posta adresi zorunludur.',
            'email.email'       => 'Geçerli bir e-posta giriniz.',
            'password.required' => 'Şifre zorunludur.',
        ]);

        $success = $this->authService->login(
            $request->email,
            $request->password,
            $request->boolean('remember')
        );

        if ($success) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Hoş geldiniz!');
        }

        return back()->withErrors(['email' => 'E-posta veya şifre hatalı.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|min:2|max:50',
            'surname'   => 'required|string|min:2|max:50',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'required|string|size:11|unique:users,phone',
            'tc_no'     => 'required|string|size:11|unique:users,tc_no',
            'birth_date'=> 'required|date|before:-18 years',
            'password'  => 'required|min:8|confirmed',
        ], [
            'name.required'      => 'Ad zorunludur.',
            'surname.required'   => 'Soyad zorunludur.',
            'email.unique'       => 'Bu e-posta adresi zaten kayıtlı.',
            'phone.unique'       => 'Bu telefon numarası zaten kayıtlı.',
            'phone.size'         => 'Telefon numarası 11 haneli olmalıdır.',
            'tc_no.unique'       => 'Bu TC kimlik numarası zaten kayıtlı.',
            'tc_no.size'         => 'TC kimlik numarası 11 haneli olmalıdır.',
            'birth_date.before'  => '18 yaşından büyük olmalısınız.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
            'password.min'       => 'Şifre en az 8 karakter olmalıdır.',
        ]);

        $user = $this->authService->register($request->all());
        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'Hesabınız başarıyla oluşturuldu! Hoş geldiniz.');
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        return redirect()->route('login')->with('success', 'Güvenli çıkış yapıldı.');
    }
}