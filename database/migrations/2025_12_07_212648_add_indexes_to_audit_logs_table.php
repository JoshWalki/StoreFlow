<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Add indexes for performance optimization
            // Check if indexes exist before creating them
            $indexes = DB::select("SHOW INDEX FROM audit_logs WHERE Key_name IN ('idx_merchant_created', 'idx_merchant_entity', 'idx_merchant_action', 'idx_merchant_user')");
            $existingIndexes = array_column($indexes, 'Key_name');

            if (!in_array('idx_merchant_created', $existingIndexes)) {
                $table->index(['merchant_id', 'created_at'], 'idx_merchant_created');
            }
            if (!in_array('idx_merchant_entity', $existingIndexes)) {
                $table->index(['merchant_id', 'entity', 'entity_id'], 'idx_merchant_entity');
            }
            if (!in_array('idx_merchant_action', $existingIndexes)) {
                $table->index(['merchant_id', 'action'], 'idx_merchant_action');
            }
            if (!in_array('idx_merchant_user', $existingIndexes)) {
                $table->index(['merchant_id', 'user_id'], 'idx_merchant_user');
            }
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
