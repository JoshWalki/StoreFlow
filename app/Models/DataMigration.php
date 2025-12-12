<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataMigration extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'platform',
        'source_url',
        'status',
        'extracted_data',
        'mapping_config',
        'import_results',
        'error_message',
        'products_found',
        'products_imported',
        'categories_found',
        'images_downloaded',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'mapping_config' => 'array',
        'import_results' => 'array',
        'products_found' => 'integer',
        'products_imported' => 'integer',
        'categories_found' => 'integer',
        'images_downloaded' => 'integer',
    ];

    /**
     * Get the store that owns this migration
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get temporary products for this migration
     */
    public function tempProducts(): HasMany
    {
        return $this->hasMany(MigrationProductTemp::class);
    }

    /**
     * Scope for specific platform
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for specific status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if migration is complete
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if migration failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if migration is ready for preview
     */
    public function isReadyForPreview(): bool
    {
        return $this->status === 'preview';
    }
}
