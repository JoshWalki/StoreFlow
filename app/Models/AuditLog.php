<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /**
     * The table doesn't have updated_at column (append-only).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'merchant_id',
        'user_id',
        'entity',
        'entity_id',
        'action',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Prevent updates to audit logs (append-only).
     */
    public static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            // Prevent updates to audit logs
            return false;
        });

        static::deleting(function ($model) {
            // Prevent deletion of audit logs
            return false;
        });
    }

    /**
     * Get the merchant that owns the audit log.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by entity type.
     */
    public function scopeForEntity($query, string $entity, int $entityId = null)
    {
        $query->where('entity', $entity);

        if ($entityId !== null) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    /**
     * Scope to filter by merchant.
     */
    public function scopeForMerchant($query, int $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent logs.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
