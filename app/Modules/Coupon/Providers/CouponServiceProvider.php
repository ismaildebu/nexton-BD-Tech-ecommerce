<?php

namespace App\Modules\Coupon\Providers;

use App\Modules\Coupon\Models\Coupon;
use App\Modules\Coupon\Policies\CouponPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CouponServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Coupon::class, CouponPolicy::class);
    }
}
