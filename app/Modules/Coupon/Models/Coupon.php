<?php

namespace App\Modules\Coupon\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'minimum_order', 'starts_at', 'expires_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
