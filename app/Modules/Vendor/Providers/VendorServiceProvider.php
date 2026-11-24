<?php

namespace App\Modules\Vendor\Providers;

use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Policies\VendorPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class VendorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Vendor::class, VendorPolicy::class);
    }
}
