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
        Schema::table('audit_logs', function (Blueprint $table) {
            // Add indexes for performance optimization
            $table->index(['merchant_id', 'created_at'], 'idx_merchant_created');
            $table->index(['merchant_id', 'entity', 'entity_id'], 'idx_merchant_entity');
            $table->index(['merchant_id', 'action'], 'idx_merchant_action');
            $table->index(['merchant_id', 'user_id'], 'idx_merchant_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_merchant_created');
            $table->dropIndex('idx_merchant_entity');
            $table->dropIndex('idx_merchant_action');
            $table->dropIndex('idx_merchant_user');
        });
    }
};
