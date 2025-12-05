<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per A1.2 Part 1 Section 5: Merchants table should include stripe_account_id
     * for future Stripe Connect integration (Standard account type).
     */
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->string('stripe_account_id', 255)
                ->nullable()
                ->after('owner_user_id')
                ->comment('Stripe Connect account ID for payment processing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn('stripe_account_id');
        });
    }
};
