<?php

namespace App\Modules\Vendor\Services;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Str;

class VendorRegistrationService
{
    public function register(User $owner, array $data): Vendor
    {
        return Vendor::create(array_merge($data, [
            'owner_user_id' => $owner->getKey(),
            'vendor_code' => 'VND-'.Str::upper(Str::random(8)),
            'verification_status' => 'pending',
            'status' => 'inactive',
            'submitted_at' => now(),
        ]));
    }
}
