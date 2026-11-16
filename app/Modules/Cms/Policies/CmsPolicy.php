<?php

namespace App\Modules\Cms\Policies;

use App\Models\User;
use App\Modules\Cms\Models\CmsPage;

class CmsPolicy
{
    public function view(User $user, CmsPage $page): bool
    {
        return $page->is_published || $user->is_admin;
    }

    public function update(User $user, CmsPage $page): bool
    {
        return $user->is_admin;
    }
}
