<?php

namespace App\Modules\Shipping\Models;

use App\Modules\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
    protected $fillable = ['order_id', 'vendor_id', 'carrier', 'tracking_number', 'status'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Vendor\Models\Vendor::class);
    }
}
