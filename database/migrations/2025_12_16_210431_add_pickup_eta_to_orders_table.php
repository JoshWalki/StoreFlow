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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('pickup_eta')->nullable()->after('pickup_time');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->integer('default_pickup_minutes')->default(30)->after('shipping_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pickup_eta');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('default_pickup_minutes');
        });
    }
};
