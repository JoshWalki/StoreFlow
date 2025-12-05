<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StoreUser extends Pivot
{
    use HasFactory;

    protected $table = 'store_users';

    protected $fillable = [
        'store_id',
        'user_id',
        'role',
    ];
}
