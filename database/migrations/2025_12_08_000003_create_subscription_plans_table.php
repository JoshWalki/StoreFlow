<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create subscription_plans table for platform subscription tiers.
     *
     * Plans:
     * - Basic: $0/month (trial) → Paid monthly
     * - Pro: Enhanced features
     * - Enterprise: Custom pricing
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->string('plan_id', 50)
                ->unique()
                ->comment('Internal plan identifier (basic, pro, enterprise)');

            $table->string('stripe_price_id', 255)
                ->unique()
                ->comment('Stripe price ID (price_xxx)');

            $table->string('stripe_product_id', 255)
                ->nullable()
                ->comment('Stripe product ID (prod_xxx)');

            $table->string('name', 100)
                ->comment('Display name (Basic Plan, Pro Plan)');

            $table->text('description')
                ->nullable()
                ->comment('Plan description for customers');

            $table->integer('price_cents')
                ->comment('Monthly price in cents (0 for trial)');

            $table->string('currency', 3)
                ->default('usd')
                ->comment('Currency code (usd, cad, gbp)');

            $table->enum('billing_interval', ['month', 'year'])
                ->default('month')
                ->comment('Billing frequency');

            $table->integer('trial_days')
                ->default(14)
                ->comment('Trial period length in days');

            $table->json('features')
                ->nullable()
                ->comment('Plan features as JSON array');

            $table->integer('max_products')
                ->nullable()
                ->comment('Maximum products allowed (null = unlimited)');

            $table->integer('max_orders_per_month')
                ->nullable()
                ->comment('Maximum orders per month (null = unlimited)');

            $table->boolean('is_active')
                ->default(true)
                ->index()
                ->comment('True if plan is available for new subscriptions');

            $table->integer('sort_order')
                ->default(0)
                ->comment('Display order on pricing page');

            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'idx_plans_active_sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
