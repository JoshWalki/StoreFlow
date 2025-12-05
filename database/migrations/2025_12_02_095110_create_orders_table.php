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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('fulfilment_type', ['pickup', 'shipping']);
            $table->enum('status', ['pending', 'accepted', 'in_progress', 'ready', 'completed', 'packing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->integer('items_total_cents');
            $table->integer('shipping_cost_cents')->default(0);
            $table->integer('total_cents');
            $table->dateTime('pickup_time')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('shipping_status')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();
            $table->string('invoice_number')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'store_id', 'status']);
            $table->index(['customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
