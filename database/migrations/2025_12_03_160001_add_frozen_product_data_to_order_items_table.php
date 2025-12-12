<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per A1.2 Part 4 Section 27: Order items must freeze product data
     * at time of purchase to prevent data corruption if product is
     * renamed, repriced, or deleted after order is placed.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use Laravel's rename method
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('qty', 'quantity');
            });
        } else {
            // MySQL/MariaDB: Use raw SQL
            DB::statement('ALTER TABLE order_items CHANGE COLUMN qty quantity INT NOT NULL');
        }

        // Then add new columns
        Schema::table('order_items', function (Blueprint $table) {
            // Frozen product identification (CRITICAL for immutability)
            $table->string('name', 255)->after('product_id')
                ->comment('Frozen product name at time of order');

            $table->string('sku', 64)->nullable()->after('name')
                ->comment('Frozen product SKU at time of order');

            // Financial breakdown fields per item
            $table->integer('line_subtotal_cents')->after('unit_price_cents')
                ->comment('Subtotal before taxes (quantity * unit_price)');

            $table->integer('tax_cents')->default(0)->after('line_subtotal_cents')
                ->comment('Tax amount for this line item');

            $table->integer('total_cents')->after('tax_cents')
                ->comment('Total including tax (line_subtotal + tax)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'sku',
                'line_subtotal_cents',
                'tax_cents',
                'total_cents',
            ]);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use Laravel's rename method
            Schema::table('order_items', function (Blueprint $table) {
                $table->renameColumn('quantity', 'qty');
            });
        } else {
            // MySQL/MariaDB: Use raw SQL
            DB::statement('ALTER TABLE order_items CHANGE COLUMN quantity qty INT NOT NULL');
        }
    }
};
