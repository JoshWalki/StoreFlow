<?php

namespace App\Providers;

use App\Models\Merchant;
use App\Observers\MerchantObserver;
use Illuminate\Support\ServiceProvider;

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
        // Register Merchant observer to handle subscription changes
        Merchant::observe(MerchantObserver::class);
    }
}
