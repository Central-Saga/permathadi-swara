<?php

namespace App\Providers;

use App\Models\Subscription;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schedule;

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
        // Auto-expire subscription yang sudah lewat tanggal berakhir
        Schedule::call(function () {
            Subscription::shouldExpire()->update(['status' => 'expired']);
        })->daily();
    }
}
