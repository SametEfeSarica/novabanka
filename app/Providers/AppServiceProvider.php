<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Uygulama Render (production) ortamında çalışıyorsa 
        // tüm linkleri ve istekleri HTTPS üzerinden gönderir.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
