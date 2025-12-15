<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add Stripe Connect and subscription fields to merchants table.
     *
     * Integration: Stripe Connect Express for merchant payment processing
     * Platform: StoreFlow multi-tenant SaaS platform
     * Webhook URL: https://dev.divvyitup.app/webhooks/stripe
     */
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            // === STRIPE CONNECT ACCOUNT FIELDS ===

            $table->string('stripe_connect_account_id', 255)
                ->nullable()
                ->after('stripe_account_id')
                ->comment('Stripe Connect Express account ID (acct_xxx)');

            $table->boolean('stripe_onboarding_complete')
                ->default(false)
                ->after('stripe_connect_account_id')
                ->comment('True when merchant completes Stripe onboarding');

            $table->boolean('stripe_charges_enabled')
                ->default(false)
                ->after('stripe_onboarding_complete')
                ->comment('True when account can accept charges');

            $table->boolean('stripe_payouts_enabled')
                ->default(false)
                ->after('stripe_charges_enabled')
                ->comment('True when account can receive payouts');

            $table->boolean('stripe_details_submitted')
                ->default(false)
                ->after('stripe_payouts_enabled')
                ->comment('True when merchant submitted required details to Stripe');

            $table->json('stripe_requirements')
                ->nullable()
                ->after('stripe_details_submitted')
                ->comment('Outstanding Stripe requirements (currently_due, eventually_due, past_due)');

            $table->string('stripe_country', 2)
                ->default('AU')
                ->after('stripe_requirements')
                ->comment('Stripe account country code (US, CA, GB, etc.)');

            $table->timestamp('stripe_verified_at')
                ->nullable()
                ->after('stripe_country')
                ->comment('When Stripe account verification completed');

            // === SUBSCRIPTION MANAGEMENT FIELDS ===

            $table->enum('subscription_status', [
                'trialing',              // Free trial period
                'active',                // Paid and current
                'past_due',              // Payment failed, in grace period
                'canceled',              // Canceled by merchant
                'unpaid',                // Payment failed, grace period ended
                'incomplete',            // Initial payment pending (23hr window)
                'incomplete_expired',    // Initial payment failed after 23hrs
                'paused'                 // Subscription paused
            ])
                ->nullable()
                ->after('stripe_verified_at')
                ->comment('Current Stripe subscription status');

            $table->string('stripe_subscription_id', 255)
                ->nullable()
                ->after('subscription_status')
                ->comment('Stripe subscription ID (sub_xxx)');

            $table->string('stripe_customer_id', 255)
                ->nullable()
                ->after('stripe_subscription_id')
                ->comment('Stripe customer ID for platform billing (cus_xxx)');

            $table->string('subscription_plan_id', 50)
                ->nullable()
                ->after('stripe_customer_id')
                ->comment('Subscription plan: basic, pro, enterprise');

            $table->timestamp('subscription_current_period_start')
                ->nullable()
                ->after('subscription_plan_id')
                ->comment('Current billing period start date');

            $table->timestamp('subscription_current_period_end')
                ->nullable()
                ->after('subscription_current_period_start')
                ->comment('Current billing period end date');

            $table->timestamp('subscription_trial_end')
                ->nullable()
                ->after('subscription_current_period_end')
                ->comment('Trial period end date (if applicable)');

            $table->boolean('subscription_cancel_at_period_end')
                ->default(false)
                ->after('subscription_trial_end')
                ->comment('True if subscription will cancel at period end');

            $table->timestamp('subscription_canceled_at')
                ->nullable()
                ->after('subscription_cancel_at_period_end')
                ->comment('When subscription was canceled');

            $table->timestamp('subscription_ended_at')
                ->nullable()
                ->after('subscription_canceled_at')
                ->comment('When subscription ended (after cancellation + grace)');

            // === PLATFORM FEE CONFIGURATION ===

            $table->decimal('platform_fee_percentage', 5, 2)
                ->default(2.90)
                ->after('subscription_ended_at')
                ->comment('Platform fee percentage (e.g., 2.90 = 2.9%)');

            $table->integer('platform_fee_fixed_cents')
                ->default(30)
                ->after('platform_fee_percentage')
                ->comment('Fixed platform fee per transaction in cents');

            // === INDEXES FOR PERFORMANCE ===

            $table->index('stripe_connect_account_id', 'idx_merchants_stripe_connect');
            $table->index('stripe_customer_id', 'idx_merchants_stripe_customer');
            $table->index('subscription_status', 'idx_merchants_subscription_status');
            $table->index(['subscription_status', 'subscription_current_period_end'], 'idx_merchants_subscription_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_merchants_stripe_connect');
            $table->dropIndex('idx_merchants_stripe_customer');
            $table->dropIndex('idx_merchants_subscription_status');
            $table->dropIndex('idx_merchants_subscription_active');

            // Drop columns
            $table->dropColumn([
                'stripe_connect_account_id',
                'stripe_onboarding_complete',
                'stripe_charges_enabled',
                'stripe_payouts_enabled',
                'stripe_details_submitted',
                'stripe_requirements',
                'stripe_country',
                'stripe_verified_at',
                'subscription_status',
                'stripe_subscription_id',
                'stripe_customer_id',
                'subscription_plan_id',
                'subscription_current_period_start',
                'subscription_current_period_end',
                'subscription_trial_end',
                'subscription_cancel_at_period_end',
                'subscription_canceled_at',
                'subscription_ended_at',
                'platform_fee_percentage',
                'platform_fee_fixed_cents',
            ]);
        });
    }
};
