<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add Stripe payment tracking fields to orders table.
     *
     * Purpose:
     * - Track Stripe payment intent IDs
     * - Record platform fees per order
     * - Store payment metadata for reconciliation
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Update payment_reference comment
            $table->string('payment_reference', 255)
                ->nullable()
                ->comment('Stripe PaymentIntent ID (pi_xxx) or other gateway reference')
                ->change();

            // Add Stripe-specific fields
            $table->string('stripe_charge_id', 255)
                ->nullable()
                ->after('payment_reference')
                ->comment('Stripe Charge ID (ch_xxx) when payment succeeds');

            $table->integer('platform_fee_cents')
                ->nullable()
                ->after('stripe_charge_id')
                ->comment('Platform fee amount in cents for this order');

            $table->integer('merchant_net_cents')
                ->nullable()
                ->after('platform_fee_cents')
                ->comment('Net amount to merchant after platform fee (in cents)');

            $table->string('stripe_transfer_id', 255)
                ->nullable()
                ->after('merchant_net_cents')
                ->comment('Stripe Transfer ID (tr_xxx) to merchant Connect account');

            $table->timestamp('stripe_transferred_at')
                ->nullable()
                ->after('stripe_transfer_id')
                ->comment('When funds transferred to merchant');

            $table->json('stripe_metadata')
                ->nullable()
                ->after('stripe_transferred_at')
                ->comment('Additional Stripe payment metadata');

            // Add indexes for Stripe lookups
            $table->index('payment_reference', 'idx_orders_payment_reference');
            $table->index('stripe_charge_id', 'idx_orders_stripe_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_payment_reference');
            $table->dropIndex('idx_orders_stripe_charge');

            $table->dropColumn([
                'stripe_charge_id',
                'platform_fee_cents',
                'merchant_net_cents',
                'stripe_transfer_id',
                'stripe_transferred_at',
                'stripe_metadata',
            ]);
        });
    }
};
