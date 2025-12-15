<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, Auditable;

    // Order status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_READY = 'ready';
    public const STATUS_PACKING = 'packing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_CANCELLED = 'cancelled';

    // Fulfilment type constants
    public const FULFILMENT_PICKUP = 'pickup';
    public const FULFILMENT_SHIPPING = 'shipping';

    // Payment status constants
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'public_id',
        'merchant_id',
        'store_id',
        'customer_id',

        // Customer snapshot (immutable contact details)
        'customer_name',
        'customer_email',
        'customer_mobile',

        // Order details
        'fulfilment_type',
        'status',

        // Payment tracking
        'payment_status',
        'payment_method',
        'payment_reference',

        // Financial breakdown
        'items_total_cents',
        'discount_cents',
        'tax_cents',
        'shipping_cost_cents',
        'total_cents',

        // Pickup details
        'pickup_time',
        'pickup_notes',

        // Shipping details
        'shipping_method',
        'shipping_status',
        'tracking_code',
        'tracking_url',
        'tracking_number',
        'courier_company',
        'shipping_name',
        'shipping_line1',
        'shipping_line2',
        'city',
        'state',
        'postcode',
        'country',

        // Lifecycle timestamps
        'placed_at',
        'accepted_at',
        'ready_at',
        'completed_at',
        'cancelled_at',

        // Other
        'invoice_number',
    ];

    protected $casts = [
        'items_total_cents' => 'integer',
        'discount_cents' => 'integer',
        'tax_cents' => 'integer',
        'shipping_cost_cents' => 'integer',
        'total_cents' => 'integer',
        'pickup_time' => 'datetime',
        'placed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'ready_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingMethodRelation(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method', 'id');
    }

    /**
     * Get metadata to include in audit logs.
     *
     * @return array
     */
    protected function getAuditMetadata(): array
    {
        return [
            'public_id' => $this->public_id,
            'customer_id' => $this->customer_id,
            'fulfilment_type' => $this->fulfilment_type,
            'total_cents' => $this->total_cents,
        ];
    }

    /**
     * Get the fields that should be audited.
     *
     * @return array
     */
    protected function getAuditableFields(): array
    {
        return [
            'status',
            'payment_status',
            'shipping_status',
            'fulfilment_type',
            'tracking_code',
            'tracking_url',
            'total_cents',
            'shipping_cost_cents',
        ];
    }
}
