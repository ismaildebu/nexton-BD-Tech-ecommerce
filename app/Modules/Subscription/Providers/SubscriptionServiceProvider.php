<?php

namespace App\Modules\Subscription\Providers;

use App\Modules\Subscription\Models\Subscription;
use App\Modules\Subscription\Policies\SubscriptionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
    }
}
