<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update platform fee from 2.90% + $0.30 to 0.30% only.
     *
     * Previous Fee Structure:
     * - 2.90% + $0.30 (matched Stripe's US processing fees - placeholder)
     *
     * New Fee Structure:
     * - 0.30% only (actual platform application fee)
     *
     * Example on $100 transaction:
     * - Before: ($100 × 2.9%) + $0.30 = $2.90 + $0.30 = $3.20
     * - After: $100 × 0.3% = $0.30
     *
     * Stripe will still charge merchants their processing fees separately.
     * This is YOUR platform fee on top of Stripe's fees.
     */
    public function up(): void
    {
        // Update all existing merchants to new fee structure
        DB::table('merchants')->update([
            'platform_fee_percentage' => 0.30,  // 0.30%
            'platform_fee_fixed_cents' => 0,    // No fixed fee
        ]);

        // Update default values for new merchants
        Schema::table('merchants', function (Blueprint $table) {
            $table->decimal('platform_fee_percentage', 5, 2)
                ->default(0.30)
                ->change()
                ->comment('Platform application fee percentage (0.30 = 0.3%)');

            $table->integer('platform_fee_fixed_cents')
                ->default(0)
                ->change()
                ->comment('Fixed platform fee per transaction in cents (set to 0)');
        });
    }

    /**
     * Reverse the migration (restore previous values).
     */
    public function down(): void
    {
        // Restore previous fee structure
        DB::table('merchants')->update([
            'platform_fee_percentage' => 2.90,  // 2.90%
            'platform_fee_fixed_cents' => 30,   // $0.30
        ]);

        // Restore previous defaults
        Schema::table('merchants', function (Blueprint $table) {
            $table->decimal('platform_fee_percentage', 5, 2)
                ->default(2.90)
                ->change()
                ->comment('Platform fee percentage (e.g., 2.90 = 2.9%)');

            $table->integer('platform_fee_fixed_cents')
                ->default(30)
                ->change()
                ->comment('Fixed platform fee per transaction in cents');
        });
    }
};
