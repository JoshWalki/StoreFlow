<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Drop indexes first, then column, then recreate
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_merchant_id_store_id_status_index');
            });
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status')->default('pending')->after('id');
                $table->index(['merchant_id', 'store_id', 'status']);
            });
        } else {
            // MySQL: Use raw SQL to modify ENUM
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending',
                'accepted',
                'in_progress',
                'ready',
                'packing',
                'shipped',
                'delivered',
                'ready_for_pickup',
                'picked_up',
                'cancelled'
            ) DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Drop indexes first, then column, then recreate
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_merchant_id_store_id_status_index');
            });
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status')->default('pending')->after('id');
                $table->index(['merchant_id', 'store_id', 'status']);
            });
        } else {
            // MySQL: Revert back to original enum values
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending',
                'accepted',
                'in_progress',
                'ready',
                'completed',
                'packing',
                'shipped',
                'delivered',
                'cancelled'
            ) DEFAULT 'pending'");
        }
    }
};
