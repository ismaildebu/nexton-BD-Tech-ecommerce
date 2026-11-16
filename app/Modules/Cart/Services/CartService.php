<?php

namespace App\Modules\Cart\Services;

use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;

class CartService
{
    public function add(int $userId, int $productId, int $quantity): CartItem
    {
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        $item = $cart->items()->firstOrNew(['product_id' => $productId]);
        $item->quantity = ($item->quantity ?? 0) + $quantity;
        $item->save();

        return $item;
    }
}
