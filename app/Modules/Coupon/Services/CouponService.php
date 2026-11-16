<?php

namespace App\Modules\Coupon\Services;

use App\Modules\Coupon\Models\Coupon;

class CouponService
{
    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }
}
