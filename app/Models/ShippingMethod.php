<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'store_id',
        'shipping_zone_id',
        'name',
        'description',
        'carrier',
        'service_code',
        'min_delivery_days',
        'max_delivery_days',
        'is_active',
        'display_order',
        'settings',
    ];

    protected $casts = [
        'min_delivery_days' => 'integer',
        'max_delivery_days' => 'integer',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'settings' => 'array',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Alias for shippingZone() for backward compatibility.
     */
    public function zone(): BelongsTo
    {
        return $this->shippingZone();
    }

    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Get the delivery time estimate as a formatted string.
     */
    public function getDeliveryEstimate(): ?string
    {
        if ($this->min_delivery_days === null && $this->max_delivery_days === null) {
            return null;
        }

        if ($this->min_delivery_days === $this->max_delivery_days) {
            return $this->min_delivery_days . ' ' . ($this->min_delivery_days === 1 ? 'day' : 'days');
        }

        if ($this->min_delivery_days === null) {
            return 'Up to ' . $this->max_delivery_days . ' days';
        }

        if ($this->max_delivery_days === null) {
            return $this->min_delivery_days . '+ days';
        }

        return $this->min_delivery_days . '-' . $this->max_delivery_days . ' days';
    }
}
