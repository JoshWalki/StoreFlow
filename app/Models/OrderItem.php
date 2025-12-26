<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',

        // Frozen product data (immutable)
        'name',
        'sku',

        // Quantity and pricing
        'quantity',
        'unit_price_cents',

        // Financial breakdown
        'line_subtotal_cents',
        'tax_cents',
        'total_cents',

        // Special instructions
        'special_instructions',

        // Refund information
        'is_refunded',
        'refund_date',
        'refund_reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
        'line_subtotal_cents' => 'integer',
        'tax_cents' => 'integer',
        'total_cents' => 'integer',
        'is_refunded' => 'boolean',
        'refund_date' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(OrderItemOption::class);
    }

    /**
     * Get the addons for this order item.
     */
    public function addons(): HasMany
    {
        return $this->hasMany(OrderItemAddon::class);
    }

    /**
     * Calculate the total price including addons.
     */
    public function calculateTotalWithAddons(): int
    {
        $addonsTotal = $this->addons()->sum('total_price_cents');
        return $this->total_cents + $addonsTotal;
    }

    /**
     * Get the total price including addons in dollars.
     */
    public function getTotalWithAddonsAttribute(): float
    {
        return $this->calculateTotalWithAddons() / 100;
    }
}
