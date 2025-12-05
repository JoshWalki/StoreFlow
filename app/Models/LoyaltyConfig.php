<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyConfig extends Model
{
    use HasFactory;

    protected $table = 'loyalty_config';

    protected $fillable = [
        'merchant_id',
        'points_per_dollar',
        'threshold',
        'reward_json',
        'is_active',
    ];

    protected $casts = [
        'points_per_dollar' => 'decimal:2',
        'threshold' => 'integer',
        'reward_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get reward configuration as array
     */
    public function getRewardConfig(): ?array
    {
        return $this->reward_json;
    }

    /**
     * Set reward configuration
     */
    public function setRewardConfig(array $config): void
    {
        $this->reward_json = $config;
    }
}
