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
        Schema::create('migration_products_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_migration_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price_cents');
            $table->string('category');
            $table->string('image_url')->nullable();
            $table->string('local_image_path')->nullable();
            $table->json('addons')->nullable();
            $table->json('variations')->nullable();
            $table->boolean('user_approved')->default(true);
            $table->boolean('imported')->default(false);
            $table->foreignId('imported_product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->timestamps();

            $table->index('data_migration_id');
            $table->index(['data_migration_id', 'user_approved']);
            $table->index('imported');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_products_temp');
    }
};
