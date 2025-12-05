<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'store_id',
        'shipping_method_id',
        'name',
        'pricing_model',
        'flat_rate_cents',
        'min_weight_grams',
        'max_weight_grams',
        'weight_rate_cents',
        'base_weight_rate_cents',
        'min_cart_total_cents',
        'max_cart_total_cents',
        'cart_total_rate_cents',
        'min_items',
        'max_items',
        'item_rate_cents',
        'free_shipping_threshold_cents',
        'is_active',
    ];

    protected $casts = [
        'flat_rate_cents' => 'integer',
        'min_weight_grams' => 'integer',
        'max_weight_grams' => 'integer',
        'weight_rate_cents' => 'integer',
        'base_weight_rate_cents' => 'integer',
        'min_cart_total_cents' => 'integer',
        'max_cart_total_cents' => 'integer',
        'cart_total_rate_cents' => 'integer',
        'min_items' => 'integer',
        'max_items' => 'integer',
        'item_rate_cents' => 'integer',
        'free_shipping_threshold_cents' => 'integer',
        'is_active' => 'boolean',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * Calculate shipping cost based on the pricing model.
     */
    public function calculateCost(int $totalWeightGrams, int $cartTotalCents, int $itemCount): ?int
    {
        // Check free shipping threshold
        if ($this->free_shipping_threshold_cents !== null && $cartTotalCents >= $this->free_shipping_threshold_cents) {
            return 0;
        }

        switch ($this->pricing_model) {
            case 'flat':
                return $this->flat_rate_cents;

            case 'weight_based':
                return $this->calculateWeightBasedCost($totalWeightGrams);

            case 'cart_total_based':
                return $this->calculateCartTotalBasedCost($cartTotalCents);

            case 'item_count':
                return $this->calculateItemCountBasedCost($itemCount);

            default:
                return null;
        }
    }

    /**
     * Check if this rate applies to the given parameters.
     */
    public function appliesTo(int $totalWeightGrams, int $cartTotalCents, int $itemCount): bool
    {
        switch ($this->pricing_model) {
            case 'weight_based':
                if ($this->min_weight_grams !== null && $totalWeightGrams < $this->min_weight_grams) {
                    return false;
                }
                if ($this->max_weight_grams !== null && $totalWeightGrams > $this->max_weight_grams) {
                    return false;
                }
                break;

            case 'cart_total_based':
                if ($this->min_cart_total_cents !== null && $cartTotalCents < $this->min_cart_total_cents) {
                    return false;
                }
                if ($this->max_cart_total_cents !== null && $cartTotalCents > $this->max_cart_total_cents) {
                    return false;
                }
                break;

            case 'item_count':
                if ($this->min_items !== null && $itemCount < $this->min_items) {
                    return false;
                }
                if ($this->max_items !== null && $itemCount > $this->max_items) {
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * Calculate weight-based shipping cost.
     */
    protected function calculateWeightBasedCost(int $totalWeightGrams): int
    {
        if ($this->base_weight_rate_cents !== null && $this->weight_rate_cents !== null) {
            // Base rate + per-gram rate
            return $this->base_weight_rate_cents + ($totalWeightGrams * $this->weight_rate_cents / 1000);
        }

        if ($this->weight_rate_cents !== null) {
            // Per-gram rate only
            return $totalWeightGrams * $this->weight_rate_cents / 1000;
        }

        return 0;
    }

    /**
     * Calculate cart total-based shipping cost.
     */
    protected function calculateCartTotalBasedCost(int $cartTotalCents): int
    {
        if ($this->cart_total_rate_cents !== null) {
            return $this->cart_total_rate_cents;
        }

        return 0;
    }

    /**
     * Calculate item count-based shipping cost.
     */
    protected function calculateItemCountBasedCost(int $itemCount): int
    {
        if ($this->item_rate_cents !== null) {
            return $itemCount * $this->item_rate_cents;
        }

        return 0;
    }
}
