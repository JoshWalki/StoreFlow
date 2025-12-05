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
            // Make all address fields nullable since addresses are stored in orders, not customers
            $table->string('address_line2')->nullable()->change();
            $table->string('address_city')->nullable()->change();
            $table->string('address_state')->nullable()->change();
            $table->string('address_postcode')->nullable()->change();
            $table->string('address_country')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revert to NOT NULL
            $table->string('address_line2')->nullable(false)->change();
            $table->string('address_city')->nullable(false)->change();
            $table->string('address_state')->nullable(false)->change();
            $table->string('address_postcode')->nullable(false)->change();
            $table->string('address_country')->nullable(false)->change();
        });
    }
};
