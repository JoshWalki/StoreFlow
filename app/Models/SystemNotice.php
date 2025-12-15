<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'bg_color',
        'text_color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the active notice (most recent).
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->latest()
            ->first();
    }
}
