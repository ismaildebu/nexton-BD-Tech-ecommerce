<?php

namespace App\Modules\Order\Services;

use App\Modules\Order\Models\Order;
use Illuminate\Support\Str;

class OrderService
{
    public function create(int $userId, float $total): Order
    {
        return Order::create([
            'user_id' => $userId,
            'order_number' => 'ORD-'.Str::upper(Str::random(8)),
            'total' => $total,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }
}
