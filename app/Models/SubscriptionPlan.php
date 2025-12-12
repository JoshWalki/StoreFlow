<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'stripe_price_id',
        'stripe_product_id',
        'name',
        'description',
        'price_cents',
        'currency',
        'billing_interval',
        'trial_days',
        'features',
        'max_products',
        'max_orders_per_month',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_cents' => 'integer',
    ];

    /**
     * Get the formatted price for display.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price_cents / 100, 2);
    }

    /**
     * Scope to get active plans only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->orderBy('sort_order');
    }

    /**
     * Check if plan has unlimited products.
     */
    public function hasUnlimitedProducts(): bool
    {
        return is_null($this->max_products);
    }

    /**
     * Check if plan has unlimited orders.
     */
    public function hasUnlimitedOrders(): bool
    {
        return is_null($this->max_orders_per_month);
    }
}
