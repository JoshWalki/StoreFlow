<?php

namespace App\Console\Commands;

use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckStoreOperatingHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stores:check-operating-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check store operating hours and automatically close stores outside their hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all stores that have operating hours set
        $stores = Store::whereNotNull('open_time')
            ->whereNotNull('close_time')
            ->where('is_active', true)
            ->get();

        $closedCount = 0;

        foreach ($stores as $store) {
            // Get current time in the store's timezone
            $timezone = $store->timezone ?? config('app.timezone');
            $currentTime = Carbon::now($timezone);

            // Parse the store's operating hours (assuming HH:MM format)
            $openTime = Carbon::parse($store->open_time, $timezone);
            $closeTime = Carbon::parse($store->close_time, $timezone);

            // Check if the store is currently outside operating hours
            $isOutsideHours = false;

            if ($closeTime->lt($openTime)) {
                // Handle overnight hours (e.g., 10:00 PM to 2:00 AM)
                // Store is open if current time is after open OR before close
                $isOutsideHours = $currentTime->lt($openTime) && $currentTime->gt($closeTime);
            } else {
                // Normal hours (e.g., 9:00 AM to 5:00 PM)
                // Store is closed if current time is before open OR after close
                $isOutsideHours = $currentTime->lt($openTime) || $currentTime->gt($closeTime);
            }

            // If outside hours, set the store to inactive
            if ($isOutsideHours) {
                $store->update(['is_active' => false]);
                $closedCount++;

                $this->info("Store '{$store->name}' (ID: {$store->id}) has been closed - outside operating hours");
            }
        }

        if ($closedCount > 0) {
            $this->info("Total stores closed: {$closedCount}");
        } else {
            $this->info('No stores needed to be closed');
        }

        return Command::SUCCESS;
    }
}
