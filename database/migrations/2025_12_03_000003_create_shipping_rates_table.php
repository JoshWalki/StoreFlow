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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_method_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable(); // Optional rate name

            // Pricing model: 'flat', 'weight_based', 'cart_total_based', 'item_count'
            $table->enum('pricing_model', ['flat', 'weight_based', 'cart_total_based', 'item_count'])->default('flat');

            // Flat rate pricing
            $table->integer('flat_rate_cents')->nullable();

            // Weight-based pricing
            $table->integer('min_weight_grams')->nullable();
            $table->integer('max_weight_grams')->nullable();
            $table->integer('weight_rate_cents')->nullable(); // Cost per unit weight
            $table->integer('base_weight_rate_cents')->nullable(); // Base cost + weight rate

            // Cart total-based pricing
            $table->integer('min_cart_total_cents')->nullable();
            $table->integer('max_cart_total_cents')->nullable();
            $table->integer('cart_total_rate_cents')->nullable();

            // Item count-based pricing
            $table->integer('min_items')->nullable();
            $table->integer('max_items')->nullable();
            $table->integer('item_rate_cents')->nullable();

            // Free shipping threshold
            $table->integer('free_shipping_threshold_cents')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['merchant_id', 'store_id', 'shipping_method_id', 'is_active'], 'sr_merchant_store_method_active_idx');
            $table->index(['pricing_model', 'is_active'], 'sr_pricing_model_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
