<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Make address fields nullable since addresses are stored in orders, not customers
            if (Schema::hasColumn('customers', 'address_line1')) {
                $table->string('address_line1')->nullable()->change();
            }
            if (Schema::hasColumn('customers', 'address_line2')) {
                $table->string('address_line2')->nullable()->change();
            }
            if (Schema::hasColumn('customers', 'city')) {
                $table->string('city')->nullable()->change();
            }
            if (Schema::hasColumn('customers', 'state')) {
                $table->string('state')->nullable()->change();
            }
            if (Schema::hasColumn('customers', 'postcode')) {
                $table->string('postcode')->nullable()->change();
            }
            if (Schema::hasColumn('customers', 'country')) {
                $table->string('country')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revert fields back to NOT NULL (if needed)
            if (Schema::hasColumn('customers', 'address_line1')) {
                $table->string('address_line1')->nullable(false)->change();
            }
            if (Schema::hasColumn('customers', 'address_line2')) {
                $table->string('address_line2')->nullable(false)->change();
            }
            if (Schema::hasColumn('customers', 'city')) {
                $table->string('city')->nullable(false)->change();
            }
            if (Schema::hasColumn('customers', 'state')) {
                $table->string('state')->nullable(false)->change();
            }
            if (Schema::hasColumn('customers', 'postcode')) {
                $table->string('postcode')->nullable(false)->change();
            }
            if (Schema::hasColumn('customers', 'country')) {
                $table->string('country')->nullable(false)->change();
            }
        });
    }
};
