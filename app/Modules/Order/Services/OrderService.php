<?php

namespace App\Modules\Order\Services;

use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Order\Models\Order;
use Illuminate\Support\Str;

class OrderService
{
    public function create(int $userId): Order
    {
        $cart = Cart::where('user_id', $userId)->with('items.product')->first();
        $total = 0.0;
        if ($cart) {
            foreach ($cart->items as $item) {
                $total += (float) $item->product->price * $item->quantity;
            }
            $cart->items()->delete();
        }

        return Order::create([
            'user_id' => $userId,
            'order_number' => 'ORD-'.Str::upper(Str::random(8)),
            'total' => $total,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }
}
