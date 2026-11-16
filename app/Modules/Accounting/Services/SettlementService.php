<?php

namespace App\Modules\Accounting\Services;

use App\Modules\Accounting\Models\Settlement;

class SettlementService
{
    public function create(int $vendorId, float $amount): Settlement
    {
        return Settlement::create([
            'vendor_id' => $vendorId,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }
}
