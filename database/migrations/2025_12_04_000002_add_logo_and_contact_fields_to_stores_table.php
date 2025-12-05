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
            if (!Schema::hasColumn('stores', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('description');
            }
            if (!Schema::hasColumn('stores', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('stores', 'contact_phone')) {
                $table->string('contact_phone', 20)->nullable()->after('contact_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'contact_email', 'contact_phone']);
        });
    }
};
