<?php

namespace App\Modules\Wishlist\Services;

use App\Modules\Wishlist\Models\Wishlist;

class WishlistService
{
    public function add(int $userId, int $productId): Wishlist
    {
        return Wishlist::firstOrCreate([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }
}
