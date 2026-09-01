<?php

namespace App\Providers;

use App\Services\Payments\MockPaymentService;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Provider default = mock
        $this->app->bind(PaymentService::class, function () {
            return new MockPaymentService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa semua URL (termasuk form action) menggunakan HTTPS di Vercel
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
