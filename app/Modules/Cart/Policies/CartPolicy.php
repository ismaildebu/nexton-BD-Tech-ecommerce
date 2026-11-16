<?php

namespace App\Modules\Cart\Policies;

use App\Models\User;
use App\Modules\Cart\Models\Cart;

class CartPolicy
{
    public function view(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }

    public function update(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }
}
