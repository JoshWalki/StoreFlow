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
        // MySQL doesn't allow ALTER on ENUM columns directly, so we need to use raw SQL
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
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
};
