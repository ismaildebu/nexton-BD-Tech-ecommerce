<?php

namespace App\Modules\Commission\Policies;

use App\Models\User;
use App\Modules\Commission\Models\Commission;

class CommissionPolicy
{
    public function view(User $user, Commission $commission): bool
    {
        return $user->is_admin || $user->vendor?->id === $commission->vendor_id;
    }
}
