<?php

namespace App\Modules\Product\Policies;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\VendorVerificationStatus;

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

    /** Vendor has an approved profile and is not suspended/blocked. */
    public function manage(User $user): bool
    {
        $vendor = $user->vendor;

        return $vendor !== null
            && $vendor->verification_status === VendorVerificationStatus::Approved
            && ! in_array($vendor->status, [VendorStatus::Suspended, VendorStatus::Blocked], true);
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->manage($user) && $user->vendor->id === $product->vendor_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}