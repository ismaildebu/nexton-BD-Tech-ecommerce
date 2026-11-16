<?php

namespace App\Modules\Accounting\Providers;

use App\Modules\Accounting\Models\Settlement;
use App\Modules\Accounting\Policies\AccountingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AccountingServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Settlement::class, AccountingPolicy::class);
    }
}
