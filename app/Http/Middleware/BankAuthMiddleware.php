<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BankAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Hesabınız askıya alınmış. Lütfen müşteri hizmetleri ile iletişime geçin.');
        }

        return $next($request);
    }
}