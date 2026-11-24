<?php

namespace App\Modules\Vendor\Services;

use App\Models\User;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\VendorVerificationStatus;
use App\Modules\Vendor\Models\Vendor;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorRegistrationService
{
    public function register(User $owner, array $data): Vendor
    {
        return DB::transaction(function () use ($owner, $data) {
            User::whereKey($owner->getKey())->lockForUpdate()->firstOrFail();
            $vendor = Vendor::where('owner_user_id', $owner->getKey())->lockForUpdate()->first();

            if ($vendor === null) {
                return Vendor::create(array_merge($data, [
                    'owner_user_id' => $owner->getKey(),
                    'vendor_code' => 'VND-'.Str::upper(Str::random(8)),
                    'verification_status' => VendorVerificationStatus::Pending,
                    'status' => VendorStatus::Inactive,
                    'submitted_at' => now(),
                ]));
            }

            if (! in_array($vendor->verification_status, [VendorVerificationStatus::Draft, VendorVerificationStatus::Rejected], true)) {
                return $vendor;
            }

            $vendor->fill($data)->forceFill([
                'verification_status' => VendorVerificationStatus::Pending,
                'status' => VendorStatus::Inactive,
                'submitted_at' => now(),
                'approved_at' => null,
                'approved_by' => null,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
            ])->save();

            return $vendor->refresh();
        });
    }

    public function resubmit(Vendor $vendor): Vendor
    {
        return DB::transaction(function () use ($vendor) {
            $locked = Vendor::whereKey($vendor->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->verification_status !== VendorVerificationStatus::Rejected) {
                throw new DomainException(__('Only a rejected application can be resubmitted.'));
            }

            $locked->forceFill([
                'verification_status' => VendorVerificationStatus::Pending,
                'status' => VendorStatus::Inactive,
                'submitted_at' => now(),
                'approved_at' => null,
                'approved_by' => null,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
            ])->save();

            return $locked;
        });
    }
}
