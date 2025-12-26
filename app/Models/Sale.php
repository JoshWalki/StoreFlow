<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'store_id',
        'name',
        'type',
        'discount_value',
        'bogo_product_id',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'discount_value' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the merchant that owns the sale.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the store that owns the sale.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the products associated with this sale.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    /**
     * Get the BOGO product (for bogo_different type).
     */
    public function bogoProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bogo_product_id');
    }

    /**
     * Check if the sale is currently active based on dates and is_active flag.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get the formatted discount display.
     */
    public function getDiscountDisplayAttribute(): string
    {
        switch ($this->type) {
            case 'price_discount':
                return '$' . number_format($this->discount_value / 100, 2) . ' off';
            case 'percent_discount':
                return $this->discount_value . '% off';
            case 'bogo_same':
                return 'Buy 1 Get 1 Free (Same Product)';
            case 'bogo_different':
                return 'Buy 1 Get 1 Free (Different Product)';
            default:
                return 'Unknown';
        }
    }
}
