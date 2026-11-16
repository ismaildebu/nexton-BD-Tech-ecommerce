<?php

namespace App\Modules\Shipping\Policies;

use App\Models\User;
use App\Modules\Shipping\Models\Shipping;

class ShippingPolicy
{
    public function view(User $user, Shipping $shipping): bool
    {
        return $user->id === $shipping->order->user_id || $user->is_admin;
    }
}
