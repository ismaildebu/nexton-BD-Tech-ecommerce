<?php

namespace App\Modules\Payment\Services;

use App\Modules\Payment\Models\Payment;

class PaymentService
{
    public function record(int $orderId, float $amount, ?string $gateway = null): Payment
    {
        return Payment::create([
            'order_id' => $orderId,
            'amount' => $amount,
            'gateway' => $gateway,
            'status' => 'pending',
        ]);
    }
}
