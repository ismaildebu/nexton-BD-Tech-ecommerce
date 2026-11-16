<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Models\Inventory;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->vendor !== null;
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->is_admin || $user->vendor?->id === $inventory->product->vendor_id;
    }
}
