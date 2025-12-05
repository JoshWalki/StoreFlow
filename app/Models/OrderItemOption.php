<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'option_id',
        'qty',
        'price_delta_cents',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price_delta_cents' => 'integer',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function customizationOption(): BelongsTo
    {
        return $this->belongsTo(CustomizationOption::class, 'option_id');
    }
}
