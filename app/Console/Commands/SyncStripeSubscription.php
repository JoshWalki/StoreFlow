<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class SyncStripeSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:sync {merchant_id?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync subscription data from Stripe to database';

    protected StripeService $stripeService;
    protected SubscriptionService $subscriptionService;

    public function __construct(StripeService $stripeService, SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->stripeService = $stripeService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            return $this->syncAllMerchants();
        }

        $merchantId = $this->argument('merchant_id');

        if (!$merchantId) {
            $this->error('Please provide a merchant_id or use --all flag');
            return 1;
        }

        return $this->syncMerchant($merchantId);
    }

    /**
     * Sync all merchants with Stripe subscriptions.
     */
    protected function syncAllMerchants(): int
    {
        $merchants = Merchant::whereNotNull('stripe_subscription_id')->get();

        $this->info("Found {$merchants->count()} merchants with subscriptions");

        $synced = 0;
        $failed = 0;

        foreach ($merchants as $merchant) {
            $this->info("Syncing merchant {$merchant->id} ({$merchant->name})...");

            try {
                $subscription = $this->stripeService->retrieveSubscription($merchant->stripe_subscription_id);
                $this->subscriptionService->syncSubscriptionFromStripe($merchant, $subscription->toArray());
                $synced++;
                $this->info("  ✓ Synced successfully");
            } catch (\Exception $e) {
                $failed++;
                $this->error("  ✗ Failed: {$e->getMessage()}");
            }
        }

        $this->info("\nSync complete:");
        $this->info("  Synced: {$synced}");
        $this->info("  Failed: {$failed}");

        return 0;
    }

    /**
     * Sync a specific merchant.
     */
    protected function syncMerchant(int $merchantId): int
    {
        $merchant = Merchant::find($merchantId);

        if (!$merchant) {
            $this->error("Merchant {$merchantId} not found");
            return 1;
        }

        $this->info("Syncing merchant {$merchant->id} ({$merchant->name})");

        // First, check if there's a stripe_customer_id
        if (!$merchant->stripe_customer_id) {
            $this->error('Merchant does not have a Stripe customer ID');
            return 1;
        }

        try {
            // Retrieve customer from Stripe
            $customer = $this->stripeService->getClient()->customers->retrieve($merchant->stripe_customer_id, [
                'expand' => ['subscriptions'],
            ]);

            if (!$customer->subscriptions || empty($customer->subscriptions->data)) {
                $this->warn('No subscriptions found for this customer in Stripe');
                $this->info('Customer ID: ' . $merchant->stripe_customer_id);
                return 1;
            }

            // Get the first (active) subscription
            $subscriptionData = $customer->subscriptions->data;
            $subscription = is_array($subscriptionData) ? $subscriptionData[0] : $subscriptionData->first();

            $this->info('Found subscription: ' . $subscription->id);
            $this->info('Status: ' . $subscription->status);

            // Sync to database
            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $subscription->toArray());

            $this->info('✓ Subscription synced successfully!');
            $this->info('');
            $this->info('Subscription Details:');
            $this->info('  ID: ' . $merchant->stripe_subscription_id);
            $this->info('  Status: ' . $merchant->subscription_status);
            $this->info('  Plan: ' . ($merchant->subscription_plan_id ?? 'N/A'));
            $this->info('  Period End: ' . ($merchant->subscription_current_period_end?->format('Y-m-d H:i:s') ?? 'N/A'));
            $this->info('  Trial End: ' . ($merchant->subscription_trial_end?->format('Y-m-d H:i:s') ?? 'N/A'));

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to sync subscription: ' . $e->getMessage());
            return 1;
        }
    }
}
