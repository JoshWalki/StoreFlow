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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Drop and recreate column (ENUM not supported)
            Schema::table('store_users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            Schema::table('store_users', function (Blueprint $table) {
                $table->string('role')->default('staff')->after('user_id');
            });
        } else {
            // MySQL: Use ENUM
            DB::statement("ALTER TABLE store_users MODIFY COLUMN role ENUM('owner', 'manager', 'staff') NOT NULL DEFAULT 'staff'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Drop and recreate column
            Schema::table('store_users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            Schema::table('store_users', function (Blueprint $table) {
                $table->string('role')->default('staff')->after('user_id');
            });
        } else {
            // MySQL: Revert ENUM
            DB::statement("ALTER TABLE store_users MODIFY COLUMN role ENUM('manager', 'staff') NOT NULL DEFAULT 'staff'");
        }
    }
};
