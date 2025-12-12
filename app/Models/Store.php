<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'name',
        'description',
        'subdomain',
        'is_active',
        'deactivated_at',
        'deactivation_reason',
        'theme_key',
        'timezone',
        'shipping_enabled',
        'logo_path',
        'contact_email',
        'contact_phone',
        'address_primary',
        'address_city',
        'address_state',
        'address_postcode',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'shipping_enabled' => 'boolean',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    /**
     * Get the merchant that owns the store.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the users assigned to this store.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_users')
            ->withPivot('role')
            ->withTimestamps();
    }
}
