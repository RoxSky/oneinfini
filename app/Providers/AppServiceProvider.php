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
        // develop menggunakan ngrok (sudah limit)
        //     if (app()->environment('local')) {
        //         URL::forceScheme('https');
        //     }
        //     DB::listen(function ($query) {
        //         Log::info("SQL: {$query->sql} [".implode(', ', $query->bindings)."] Time: {$query->time} ms");
        //     });
    }
}
