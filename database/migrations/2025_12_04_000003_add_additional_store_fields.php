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
            // Add subdomain if it doesn't exist
            if (!Schema::hasColumn('stores', 'subdomain')) {
                $table->string('subdomain', 63)->nullable()->after('name');
            }

            // Add is_active if it doesn't exist (smallint represented as tinyInteger in Laravel)
            if (!Schema::hasColumn('stores', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('subdomain');
            }

            // Add address fields if they don't exist
            if (!Schema::hasColumn('stores', 'address_primary')) {
                $table->string('address_primary')->nullable()->after('contact_phone');
            }

            if (!Schema::hasColumn('stores', 'address_city')) {
                $table->string('address_city', 100)->nullable()->after('address_primary');
            }

            if (!Schema::hasColumn('stores', 'address_state')) {
                $table->string('address_state', 100)->nullable()->after('address_city');
            }

            if (!Schema::hasColumn('stores', 'address_postcode')) {
                $table->string('address_postcode', 20)->nullable()->after('address_state');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'subdomain',
                'is_active',
                'address_primary',
                'address_city',
                'address_state',
                'address_postcode',
            ]);
        });
    }
};
