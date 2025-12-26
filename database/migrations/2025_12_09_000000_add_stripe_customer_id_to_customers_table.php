<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('email');
            }
        });

        // Add index separately to avoid conflicts
        Schema::table('customers', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEX FROM customers WHERE Key_name = 'customers_stripe_customer_id_index'");
            if (empty($indexes)) {
                $table->index('stripe_customer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['stripe_customer_id']);
            $table->dropColumn('stripe_customer_id');
        });
    }
};
