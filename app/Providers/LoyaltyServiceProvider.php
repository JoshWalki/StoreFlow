<?php

namespace App\Providers;

use App\Services\Loyalty\LoyaltyService;
use Illuminate\Support\ServiceProvider;

class LoyaltyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LoyaltyService::class, function ($app) {
            return new LoyaltyService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
