<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'merchant_id',
        'points_balance',
        'points_earned',
        'points_redeemed',
        'lifetime_points',
        'last_activity_at',
    ];

    protected $casts = [
        'points_balance' => 'integer',
        'points_earned' => 'integer',
        'points_redeemed' => 'integer',
        'lifetime_points' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Check if account has sufficient points
     */
    public function hasSufficientPoints(int $points): bool
    {
        return $this->points_balance >= $points;
    }

    /**
     * Check if account has reached threshold
     */
    public function hasReachedThreshold(int $threshold): bool
    {
        return $this->points_balance >= $threshold;
    }
}
