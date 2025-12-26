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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Summer Sale", "BOGO Friday"
            $table->enum('type', ['price_discount', 'percent_discount', 'bogo_same', 'bogo_different']);
            $table->integer('discount_value')->nullable(); // cents for price_discount, percentage for percent_discount
            $table->foreignId('bogo_product_id')->nullable()->constrained('products')->onDelete('set null'); // For BOGO different product
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'store_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
