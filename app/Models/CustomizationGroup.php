<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomizationGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'min_select',
        'max_select',
        'required',
    ];

    protected $casts = [
        'min_select' => 'integer',
        'max_select' => 'integer',
        'required' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(CustomizationOption::class, 'group_id');
    }
}
