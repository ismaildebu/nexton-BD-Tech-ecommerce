<?php

namespace App\Modules\Cart\Providers;

use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Policies\CartPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Cart::class, CartPolicy::class);
    }
}
