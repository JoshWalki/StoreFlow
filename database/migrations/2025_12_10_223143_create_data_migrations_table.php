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
        Schema::create('data_migrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->enum('platform', ['ubereats', 'doordash', 'menulog', 'deliveroo']);
            $table->string('source_url');
            $table->enum('status', [
                'pending',
                'scraping',
                'preview',
                'importing',
                'completed',
                'failed'
            ])->default('pending');
            $table->json('extracted_data')->nullable();
            $table->json('mapping_config')->nullable();
            $table->json('import_results')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('products_found')->default(0);
            $table->integer('products_imported')->default(0);
            $table->integer('categories_found')->default(0);
            $table->integer('images_downloaded')->default(0);
            $table->timestamps();

            $table->index(['store_id', 'status']);
            $table->index('platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_migrations');
    }
};
