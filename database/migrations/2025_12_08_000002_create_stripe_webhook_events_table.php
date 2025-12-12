<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create stripe_webhook_events table for webhook processing and idempotency.
     *
     * Purpose:
     * - Log all incoming Stripe webhook events
     * - Prevent duplicate processing via event_id uniqueness
     * - Track processing status and errors
     * - Provide audit trail for debugging
     */
    public function up(): void
    {
        Schema::create('stripe_webhook_events', function (Blueprint $table) {
            $table->id();

            $table->string('event_id', 255)
                ->unique()
                ->comment('Stripe event ID (evt_xxx) - ensures idempotency');

            $table->string('type', 100)
                ->index()
                ->comment('Event type (customer.subscription.updated, etc.)');

            $table->foreignId('merchant_id')
                ->nullable()
                ->constrained('merchants')
                ->nullOnDelete()
                ->comment('Related merchant if applicable');

            $table->json('payload')
                ->comment('Complete webhook payload from Stripe');

            $table->boolean('processed')
                ->default(false)
                ->index()
                ->comment('True when event processing completed successfully');

            $table->timestamp('processed_at')
                ->nullable()
                ->comment('When event was processed');

            $table->text('exception')
                ->nullable()
                ->comment('Exception message if processing failed');

            $table->integer('retry_count')
                ->default(0)
                ->comment('Number of processing retry attempts');

            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['type', 'processed', 'created_at'], 'idx_events_type_status');
            $table->index(['merchant_id', 'created_at'], 'idx_events_merchant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_events');
    }
};
