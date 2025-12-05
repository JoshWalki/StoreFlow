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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('entity'); // e.g., 'Order', 'Product', 'ShippingConfig'
            $table->unsignedBigInteger('entity_id'); // ID of the entity being audited
            $table->string('action'); // e.g., 'created', 'updated', 'deleted', 'status_changed'
            $table->json('meta_json')->nullable(); // Additional metadata about the action
            $table->timestamp('created_at'); // Only created_at, no updated_at (append-only)

            // Indexes for efficient querying
            $table->index(['merchant_id', 'entity', 'entity_id']);
            $table->index(['merchant_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['entity', 'entity_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
