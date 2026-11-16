<?php

namespace App\Modules\Accounting\Policies;

use App\Models\User;
use App\Modules\Accounting\Models\Settlement;

class AccountingPolicy
{
    public function view(User $user, Settlement $settlement): bool
    {
        return $user->is_admin || $user->vendor?->id === $settlement->vendor_id;
    }
}
