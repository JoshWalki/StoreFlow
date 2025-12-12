<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Check for overdue subscriptions and deactivate stores.
 *
 * This command should run daily via cron to check if any merchant
 * subscriptions are 3+ days overdue and deactivate their stores.
 *
 * Schedule in app/Console/Kernel.php:
 * $schedule->command('subscriptions:check-overdue')->daily();
 */
class CheckOverdueSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-overdue {--dry-run : Show what would be done without making changes}';

    protected $description = 'Check for overdue subscriptions (3+ days) and deactivate stores';

    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    public function handle(): int
    {
        $this->info('Checking for overdue subscriptions...');

        $overdueMerchants = Merchant::where('subscription_status', 'past_due')
            ->whereNotNull('subscription_current_period_end')
            ->with('stores')
            ->get();

        $this->info("Found {$overdueMerchants->count()} merchants with past_due status");

        $deactivatedCount = 0;
        $merchantsProcessed = 0;

        foreach ($overdueMerchants as $merchant) {
            if (!$this->subscriptionService->isSubscriptionOverdue($merchant)) {
                continue; // Not yet 3 days overdue
            }

            $merchantsProcessed++;

            // Count active stores
            $activeStores = $merchant->stores->where('is_active', true)->count();

            if ($activeStores === 0) {
                $this->line("  Merchant #{$merchant->id} ({$merchant->name}) - No active stores to deactivate");
                continue;
            }

            $this->warn("  Merchant #{$merchant->id} ({$merchant->name}) - {$activeStores} active stores");
            $this->warn("    Subscription overdue since: {$merchant->subscription_current_period_end->addDays(3)->toDateString()}");

            if ($this->option('dry-run')) {
                $this->warn("    [DRY RUN] Would deactivate {$activeStores} stores");
            } else {
                $deactivated = $this->subscriptionService->deactivateStores($merchant, 'subscription_overdue');
                $this->error("    ✗ Deactivated {$deactivated} stores");
                $deactivatedCount += $deactivated;

                // TODO: Send notification to merchant
                // Notification::send($merchant->owner, new StoresDeactivatedNotification($merchant));
            }
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info("DRY RUN COMPLETE");
            $this->info("Would process {$merchantsProcessed} overdue merchants");
        } else {
            $this->info("COMPLETE");
            $this->info("Processed {$merchantsProcessed} overdue merchants");
            $this->info("Deactivated {$deactivatedCount} stores");

            Log::info('Overdue subscriptions check completed', [
                'merchants_processed' => $merchantsProcessed,
                'stores_deactivated' => $deactivatedCount,
            ]);
        }

        return self::SUCCESS;
    }
}
