<?php

namespace App\Observers;

use App\Models\Merchant;
use Illuminate\Support\Facades\Log;

class MerchantObserver
{
    /**
     * Handle the Merchant "updated" event.
     * Automatically deactivate all stores when subscription becomes inactive.
     */
    public function updated(Merchant $merchant)
    {
        // Check if subscription_status changed
        if ($merchant->isDirty('subscription_status')) {
            // If subscription is not active (using Merchant's own logic)
            if (!$merchant->hasActiveSubscription()) {
                $storesCount = $merchant->stores()->where('is_active', true)->count();

                if ($storesCount > 0) {
                    $merchant->stores()->update(['is_active' => false]);

                    $status = $merchant->subscription_status ?? 'NULL';
                    Log::info("Merchant ID {$merchant->id}: Deactivated {$storesCount} store(s) due to subscription status change to: {$status}");
                }
            }
        }
    }
}
