<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomizationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'price_delta_cents',
        'max_quantity',
    ];

    protected $casts = [
        'price_delta_cents' => 'integer',
        'max_quantity' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomizationGroup::class, 'group_id');
    }

    public function customizationGroup(): BelongsTo
    {
        return $this->belongsTo(CustomizationGroup::class, 'group_id');
    }
}
