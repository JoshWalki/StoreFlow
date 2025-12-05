<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per A1.2 Part 5 Section 36: Shipping methods must specify their calculation type.
     * This determines how the shipping cost is calculated for orders.
     */
    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->enum('type', ['flat', 'weight', 'price', 'formula'])
                ->default('flat')
                ->after('name')
                ->comment('Calculation type: flat=fixed rate, weight=per kg, price=percentage, formula=custom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
