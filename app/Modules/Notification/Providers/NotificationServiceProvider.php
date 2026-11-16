<?php

namespace App\Modules\Notification\Providers;

use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Policies\NotificationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Notification::class, NotificationPolicy::class);
    }
}
