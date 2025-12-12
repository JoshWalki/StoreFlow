<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'product_addon_id',
        'name',
        'description',
        'quantity',
        'unit_price_cents',
        'total_price_cents',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
        'total_price_cents' => 'integer',
    ];

    /**
     * Get the order item that owns this addon.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the product addon (nullable since it can be deleted).
     */
    public function productAddon(): BelongsTo
    {
        return $this->belongsTo(ProductAddon::class);
    }

    /**
     * Get the unit price in dollars.
     */
    public function getUnitPriceAttribute(): float
    {
        return $this->unit_price_cents / 100;
    }

    /**
     * Get the total price in dollars.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->total_price_cents / 100;
    }

    /**
     * Get formatted unit price.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }
}
