<?php

namespace App\Modules\Product\Policies;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\VendorVerificationStatus;
use App\Modules\Vendor\Models\Vendor;

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

    /** Vendor has an active, approved profile and an active owner or staff membership. */
    public function manage(User $user): bool
    {
        $vendor = Vendor::accessibleTo($user)->first();

        return $vendor !== null
            && $vendor->verification_status === VendorVerificationStatus::Approved
            && $vendor->status === VendorStatus::Active;
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->manage($user)
            && Vendor::accessibleTo($user)->whereKey($product->vendor_id)->exists();
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}