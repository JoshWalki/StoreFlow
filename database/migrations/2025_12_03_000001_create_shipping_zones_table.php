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
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('countries')->nullable(); // Array of country codes
            $table->json('states')->nullable(); // Array of state codes
            $table->json('postcodes')->nullable(); // Array of postcode patterns
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority zones are checked first
            $table->timestamps();

            $table->index(['merchant_id', 'store_id', 'is_active']);
            $table->index(['merchant_id', 'store_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
