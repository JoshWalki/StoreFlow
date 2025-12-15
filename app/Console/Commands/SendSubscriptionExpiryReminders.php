<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Mail\SubscriptionExpiringMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendSubscriptionExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-expiry-reminders
                            {--days=2 : Number of days before expiry to send reminder}
                            {--dry-run : Run without sending emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to merchants whose subscriptions are expiring soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysBeforeExpiry = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Checking for subscriptions expiring in {$daysBeforeExpiry} days...");

        // Calculate the target date
        $targetDate = Carbon::now()->addDays($daysBeforeExpiry)->startOfDay();
        $targetDateEnd = $targetDate->copy()->endOfDay();

        $this->info("Target date: {$targetDate->format('Y-m-d')}");

        // Find merchants with subscriptions expiring on the target date
        $merchants = Merchant::whereNotNull('subscription_current_period_end')
            ->whereIn('subscription_status', ['active', 'trialing', 'past_due'])
            ->whereBetween('subscription_current_period_end', [
                $targetDate,
                $targetDateEnd
            ])
            ->with('owner')
            ->get();

        if ($merchants->isEmpty()) {
            $this->info('No subscriptions expiring in ' . $daysBeforeExpiry . ' days.');
            Log::info('Subscription expiry reminder check - no expiring subscriptions found', [
                'days_before_expiry' => $daysBeforeExpiry,
                'target_date' => $targetDate->format('Y-m-d'),
            ]);
            return 0;
        }

        $this->info("Found {$merchants->count()} subscription(s) expiring soon.");

        $emailsSent = 0;
        $emailsFailed = 0;

        foreach ($merchants as $merchant) {
            if (!$merchant->owner || !$merchant->owner->email) {
                $this->warn("Skipping merchant {$merchant->id} - no owner email found");
                $emailsFailed++;
                continue;
            }

            $expiryDate = $merchant->subscription_current_period_end->format('Y-m-d H:i:s');
            $this->line("Processing: {$merchant->name} (ID: {$merchant->id}) - Expires: {$expiryDate}");

            if ($dryRun) {
                $this->info("  [DRY RUN] Would send email to: {$merchant->owner->email}");
                $emailsSent++;
                continue;
            }

            try {
                Mail::to($merchant->owner->email)->send(new SubscriptionExpiringMail($merchant));

                $this->info("  ✓ Email sent to: {$merchant->owner->email}");
                $emailsSent++;

                Log::info('Subscription expiry reminder sent', [
                    'merchant_id' => $merchant->id,
                    'merchant_name' => $merchant->name,
                    'owner_email' => $merchant->owner->email,
                    'expiry_date' => $expiryDate,
                    'days_until_expiry' => $daysBeforeExpiry,
                ]);

            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send email to: {$merchant->owner->email}");
                $this->error("    Error: {$e->getMessage()}");
                $emailsFailed++;

                Log::error('Failed to send subscription expiry reminder', [
                    'merchant_id' => $merchant->id,
                    'merchant_name' => $merchant->name,
                    'owner_email' => $merchant->owner->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Total subscriptions expiring: {$merchants->count()}");
        $this->info("  Emails sent: {$emailsSent}");
        if ($emailsFailed > 0) {
            $this->error("  Emails failed: {$emailsFailed}");
        }

        Log::info('Subscription expiry reminders completed', [
            'days_before_expiry' => $daysBeforeExpiry,
            'total_expiring' => $merchants->count(),
            'emails_sent' => $emailsSent,
            'emails_failed' => $emailsFailed,
            'dry_run' => $dryRun,
        ]);

        return 0;
    }
}
