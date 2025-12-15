<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check store operating hours every minute
        $schedule->command('stores:check-operating-hours')->everyMinute();

        // Send subscription expiry reminders daily at 9 AM
        // Reminds merchants 2 days before their subscription expires
        $schedule->command('subscriptions:send-expiry-reminders --days=2')
                 ->dailyAt('09:00')
                 ->timezone('Australia/Melbourne');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
