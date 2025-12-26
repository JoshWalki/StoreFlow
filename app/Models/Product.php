<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'store_id',
        'category_id',
        'name',
        'description',
        'price_cents',
        'image_path',
        'is_active',
        'is_featured',
        'addon_data',
        'weight_grams',
        'length_cm',
        'width_cm',
        'height_cm',
        'is_shippable',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'weight_grams' => 'integer',
        'length_cm' => 'integer',
        'width_cm' => 'integer',
        'height_cm' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_shippable' => 'boolean',
        'addon_data' => 'array',
    ];

    protected $appends = ['addons'];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function customizationGroups(): HasMany
    {
        return $this->hasMany(CustomizationGroup::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get the sales associated with this product.
     */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class)->withTimestamps();
    }

    /**
     * Get the currently active sale for this product.
     */
    public function activeSale()
    {
        return $this->sales()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->first();
    }

    /**
     * Get addons attribute - returns addon_data for compatibility.
     */
    public function getAddonsAttribute()
    {
        // Access the casted addon_data value through the array access
        $data = $this->getAttributeValue('addon_data');
        return $data ?? [];
    }

    /**
     * Check if the product has an active sale.
     */
    public function hasActiveSale(): bool
    {
        return $this->activeSale() !== null;
    }

    /**
     * Get the sale price in cents (price after discount).
     * Returns original price if no active sale.
     */
    public function getSalePriceAttribute(): int
    {
        $sale = $this->activeSale();

        if (!$sale) {
            return $this->price_cents;
        }

        switch ($sale->type) {
            case 'price_discount':
                // Subtract fixed amount discount
                return max(0, $this->price_cents - $sale->discount_value);

            case 'percent_discount':
                // Apply percentage discount
                $discount = ($this->price_cents * $sale->discount_value) / 100;
                return max(0, (int) ($this->price_cents - $discount));

            case 'bogo_same':
            case 'bogo_different':
                // BOGO doesn't change individual item price
                // Discount is applied at cart level
                return $this->price_cents;

            default:
                return $this->price_cents;
        }
    }

    /**
     * Get the active sale model (for detailed information).
     */
    public function getActiveSaleAttribute(): ?Sale
    {
        return $this->activeSale();
    }

    /**
     * Get the discount percentage for display.
     * Returns null if no active sale or if BOGO.
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        $sale = $this->activeSale();

        if (!$sale) {
            return null;
        }

        switch ($sale->type) {
            case 'percent_discount':
                return $sale->discount_value;

            case 'price_discount':
                // Calculate percentage from fixed amount
                if ($this->price_cents > 0) {
                    return (int) (($sale->discount_value / $this->price_cents) * 100);
                }
                return null;

            default:
                return null;
        }
    }

    /**
     * Get the savings amount in cents.
     */
    public function getSavingsAmountAttribute(): int
    {
        return max(0, $this->price_cents - $this->sale_price);
    }

    /**
     * Get a formatted discount badge text.
     */
    public function getDiscountBadgeAttribute(): ?string
    {
        $sale = $this->activeSale();

        if (!$sale) {
            return null;
        }

        switch ($sale->type) {
            case 'percent_discount':
                return $sale->discount_value . '% OFF';

            case 'price_discount':
                return '$' . number_format($sale->discount_value / 100, 2) . ' OFF';

            case 'bogo_same':
                return 'BOGO';

            case 'bogo_different':
                return 'BOGO';

            default:
                return 'SALE';
        }
    }
}
