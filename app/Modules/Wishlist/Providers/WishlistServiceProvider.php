<?php

namespace App\Modules\Wishlist\Providers;

use App\Modules\Wishlist\Models\Wishlist;
use App\Modules\Wishlist\Policies\WishlistPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class WishlistServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Wishlist::class, WishlistPolicy::class);
    }
}
