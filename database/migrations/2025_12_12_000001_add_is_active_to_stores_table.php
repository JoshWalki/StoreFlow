<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add is_active field to stores table for subscription-based deactivation.
     *
     * Store deactivation occurs when subscription is 3+ days overdue.
     * Re-activation requires payment settlement check.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Check if is_active already exists (may have been added manually)
            if (!Schema::hasColumn('stores', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('close_time')
                    ->index()
                    ->comment('Store active status - deactivated when subscription >3 days overdue');
            }

            if (!Schema::hasColumn('stores', 'deactivated_at')) {
                $table->timestamp('deactivated_at')
                    ->nullable()
                    ->after('is_active')
                    ->comment('Timestamp when store was deactivated due to payment issues');
            }

            if (!Schema::hasColumn('stores', 'deactivation_reason')) {
                $table->text('deactivation_reason')
                    ->nullable()
                    ->after('deactivated_at')
                    ->comment('Reason for store deactivation (subscription_overdue, manual, etc)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'deactivated_at', 'deactivation_reason']);
        });
    }
};
