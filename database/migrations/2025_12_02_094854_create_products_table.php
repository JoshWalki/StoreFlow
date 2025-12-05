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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price_cents');
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('weight_grams')->nullable();
            $table->integer('length_cm')->nullable();
            $table->integer('width_cm')->nullable();
            $table->integer('height_cm')->nullable();
            $table->boolean('is_shippable')->default(true);
            $table->timestamps();

            $table->index(['merchant_id', 'category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
