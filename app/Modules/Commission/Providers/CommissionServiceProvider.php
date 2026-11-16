<?php

namespace App\Modules\Commission\Providers;

use App\Modules\Commission\Models\Commission;
use App\Modules\Commission\Policies\CommissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CommissionServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Commission::class, CommissionPolicy::class);
    }
}
