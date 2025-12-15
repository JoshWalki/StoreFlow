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
        Schema::create('system_notices', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('bg_color', 7)->default('#3b82f6'); // Default blue background
            $table->string('text_color', 7)->default('#ffffff'); // Default white text
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notices');
    }
};
