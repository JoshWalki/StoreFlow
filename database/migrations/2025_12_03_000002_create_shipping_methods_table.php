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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_zone_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('carrier')->nullable(); // e.g., 'auspost', 'fedex', 'ups'
            $table->string('service_code')->nullable(); // e.g., 'express', 'standard'
            $table->integer('min_delivery_days')->nullable();
            $table->integer('max_delivery_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->json('settings')->nullable(); // Additional carrier-specific settings
            $table->timestamps();

            $table->index(['merchant_id', 'store_id', 'shipping_zone_id', 'is_active'], 'sm_merchant_store_zone_active_idx');
            $table->index(['merchant_id', 'store_id', 'is_active', 'display_order'], 'sm_merchant_store_active_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
