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
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_refunded')->default(false)->after('special_instructions');
            $table->timestamp('refund_date')->nullable()->after('is_refunded');
            $table->string('refund_reason')->nullable()->after('refund_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_refunded', 'refund_date', 'refund_reason']);
        });
    }
};
