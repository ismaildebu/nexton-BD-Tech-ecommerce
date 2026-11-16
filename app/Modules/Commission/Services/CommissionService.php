<?php

namespace App\Modules\Commission\Services;

use App\Modules\Commission\Models\Commission;

class CommissionService
{
    public function record(int $vendorId, float $amount, float $rate = 0, ?int $orderId = null): Commission
    {
        return Commission::create([
            'vendor_id' => $vendorId,
            'order_id' => $orderId,
            'amount' => $amount,
            'rate' => $rate,
        ]);
    }
}
