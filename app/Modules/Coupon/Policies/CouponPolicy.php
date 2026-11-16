<?php

namespace App\Modules\Coupon\Policies;

use App\Models\User;
use App\Modules\Coupon\Models\Coupon;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }
}
