<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MigrationProductTemp extends Model
{
    use HasFactory;

    protected $table = 'migration_products_temp';

    protected $fillable = [
        'data_migration_id',
        'name',
        'description',
        'price_cents',
        'category',
        'image_url',
        'local_image_path',
        'addons',
        'variations',
        'user_approved',
        'imported',
        'imported_product_id',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'addons' => 'array',
        'variations' => 'array',
        'user_approved' => 'boolean',
        'imported' => 'boolean',
    ];

    /**
     * Get the data migration that owns this product
     */
    public function dataMigration(): BelongsTo
    {
        return $this->belongsTo(DataMigration::class);
    }

    /**
     * Get the imported product if exists
     */
    public function importedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'imported_product_id');
    }

    /**
     * Scope for approved products only
     */
    public function scopeApproved($query)
    {
        return $query->where('user_approved', true);
    }

    /**
     * Scope for not yet imported products
     */
    public function scopeNotImported($query)
    {
        return $query->where('imported', false);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price_cents / 100, 2);
    }
}
