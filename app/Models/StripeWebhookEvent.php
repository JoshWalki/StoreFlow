<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'type',
        'merchant_id',
        'payload',
        'processed',
        'processed_at',
        'exception',
        'retry_count',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the merchant associated with this webhook event.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Check if this event has already been processed.
     */
    public function isProcessed(): bool
    {
        return $this->processed === true;
    }

    /**
     * Mark event as processed successfully.
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'exception' => null,
        ]);
    }

    /**
     * Mark event as failed with exception.
     */
    public function markAsFailed(string $exception): void
    {
        $this->update([
            'processed' => false,
            'exception' => $exception,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Scope to get unprocessed events.
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope to get events by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
