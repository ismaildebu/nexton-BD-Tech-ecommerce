<?php

namespace App\Modules\Shipping\Services;

use App\Modules\Shipping\Models\Shipping;

class ShippingService
{
    public function create(int $orderId, ?string $carrier = null, ?string $tracking = null): Shipping
    {
        return Shipping::create([
            'order_id' => $orderId,
            'carrier' => $carrier,
            'tracking_number' => $tracking,
            'status' => 'pending',
        ]);
    }
}
