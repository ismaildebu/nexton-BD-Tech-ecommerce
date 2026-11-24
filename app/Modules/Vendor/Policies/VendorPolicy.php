<?php

namespace App\Modules\Vendor\Policies;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;

class VendorPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return Vendor::accessibleTo($user)->exists();
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $this->manage($user, $vendor);
    }

    public function manage(User $user, Vendor $vendor): bool
    {
        return Vendor::accessibleTo($user)->whereKey($vendor->getKey())->exists();
    }

    public function manageStaff(User $user, Vendor $vendor): bool
    {
        return $user->id === $vendor->owner_user_id;
    }

    public function manageProfile(User $user, Vendor $vendor): bool
    {
        return $user->id === $vendor->owner_user_id;
    }
}
