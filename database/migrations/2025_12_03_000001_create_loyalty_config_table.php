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
        Schema::create('loyalty_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->decimal('points_per_dollar', 8, 2)->default(1.00);
            $table->integer('threshold')->default(100); // Points threshold for reward
            $table->json('reward_json')->nullable(); // Store reward details as JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure one config per merchant
            $table->unique('merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_config');
    }
};
