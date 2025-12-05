<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per A1.2 Part 2 Section 7: Products should support optional store-level scoping.
     * This allows merchants with multiple stores to have store-specific products
     * in addition to merchant-wide products.
     *
     * When store_id is NULL, the product is available to all stores under the merchant.
     * When store_id is set, the product is only available to that specific store.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->nullable()
                ->after('merchant_id')
                ->constrained('stores')
                ->onDelete('cascade')
                ->comment('Optional: Restricts product to specific store. NULL = available to all merchant stores');

            // Add indexes for query performance
            $table->index('merchant_id', 'idx_products_merchant');
            $table->index('store_id', 'idx_products_store');
            $table->index(['merchant_id', 'store_id', 'is_active'], 'idx_products_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_merchant');
            $table->dropIndex('idx_products_store');
            $table->dropIndex('idx_products_active');

            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
