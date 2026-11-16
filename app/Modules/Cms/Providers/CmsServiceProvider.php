<?php

namespace App\Modules\Cms\Providers;

use App\Modules\Cms\Models\CmsPage;
use App\Modules\Cms\Policies\CmsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CmsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(CmsPage::class, CmsPolicy::class);
    }
}
