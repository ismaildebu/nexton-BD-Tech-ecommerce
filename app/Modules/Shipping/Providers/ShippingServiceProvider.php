<?php

namespace App\Modules\Shipping\Providers;

use App\Modules\Shipping\Models\Shipping;
use App\Modules\Shipping\Policies\ShippingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Shipping::class, ShippingPolicy::class);
    }
}
