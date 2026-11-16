<?php

namespace App\Modules\Product\Providers;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
    }
}
