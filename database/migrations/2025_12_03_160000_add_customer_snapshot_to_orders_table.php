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
     * Per A1.2 Part 4 Section 29: Orders must be immutable.
     * Customer contact details must be frozen at order creation time
     * to prevent data corruption if customer updates their profile.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Each rename must be in separate Schema::table() call
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('line1', 'shipping_line1');
            });
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('line2', 'shipping_line2');
            });
        } else {
            // MySQL/MariaDB: Use raw SQL
            DB::statement('ALTER TABLE orders CHANGE COLUMN line1 shipping_line1 VARCHAR(255) NULL');
            DB::statement('ALTER TABLE orders CHANGE COLUMN line2 shipping_line2 VARCHAR(255) NULL');
        }

        // Then add new columns
        Schema::table('orders', function (Blueprint $table) {
            // Customer snapshot fields (CRITICAL for immutability)
            $table->string('customer_name', 255)->nullable()->after('customer_id');
            $table->string('customer_email', 255)->nullable()->after('customer_name');
            $table->string('customer_mobile', 50)->nullable()->after('customer_email');

            // Payment tracking fields
            $table->string('payment_method', 50)->nullable()->after('payment_status');
            $table->string('payment_reference', 191)->nullable()->after('payment_method');

            // Financial breakdown fields
            $table->integer('discount_cents')->default(0)->after('items_total_cents');
            $table->integer('tax_cents')->default(0)->after('discount_cents');

            // Order lifecycle timestamps
            $table->datetime('placed_at')->nullable()->after('created_at');
            $table->datetime('accepted_at')->nullable()->after('placed_at');
            $table->datetime('ready_at')->nullable()->after('accepted_at');
            $table->datetime('completed_at')->nullable()->after('ready_at');
            $table->datetime('cancelled_at')->nullable()->after('completed_at');

            // Note: Column renaming will be done with raw SQL due to MariaDB compatibility

            // Add pickup notes field (missing from original migration)
            $table->text('pickup_notes')->nullable()->after('pickup_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_email',
                'customer_mobile',
                'payment_method',
                'payment_reference',
                'discount_cents',
                'tax_cents',
                'placed_at',
                'accepted_at',
                'ready_at',
                'completed_at',
                'cancelled_at',
                'pickup_notes',
            ]);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Each rename must be in separate Schema::table() call
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('shipping_line1', 'line1');
            });
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('shipping_line2', 'line2');
            });
        } else {
            // MySQL/MariaDB: Use raw SQL
            DB::statement('ALTER TABLE orders CHANGE COLUMN shipping_line1 line1 VARCHAR(255) NULL');
            DB::statement('ALTER TABLE orders CHANGE COLUMN shipping_line2 line2 VARCHAR(255) NULL');
        }
    }
};
