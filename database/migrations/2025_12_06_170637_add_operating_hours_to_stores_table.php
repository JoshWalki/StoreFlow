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
        Schema::table('stores', function (Blueprint $table) {
            // Add operating hours columns if they don't exist
            if (!Schema::hasColumn('stores', 'open_time')) {
                $table->time('open_time')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('stores', 'close_time')) {
                $table->time('close_time')->nullable()->after('open_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['open_time', 'close_time']);
        });
    }
};
