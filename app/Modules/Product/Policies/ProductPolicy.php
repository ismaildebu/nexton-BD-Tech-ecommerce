<?php

namespace App\Modules\Product\Policies;

use App\Models\User;
use App\Modules\Product\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->vendor !== null;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->vendor?->id === $product->vendor_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->vendor?->id === $product->vendor_id;
    }
}
