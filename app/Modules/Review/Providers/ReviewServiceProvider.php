<?php

namespace App\Modules\Review\Providers;

use App\Modules\Review\Models\Review;
use App\Modules\Review\Policies\ReviewPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ReviewServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Review::class, ReviewPolicy::class);
    }
}
