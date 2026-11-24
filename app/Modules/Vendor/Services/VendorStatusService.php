<?php

namespace App\Modules\Vendor\Services;

use App\Models\User;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\VendorVerificationStatus;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorStatusHistory;
use DomainException;
use Illuminate\Support\Facades\DB;

class VendorStatusService
{
    public function approve(Vendor $vendor, User $admin): Vendor
    {
        return DB::transaction(function () use ($vendor, $admin) {
            $locked = Vendor::whereKey($vendor->getKey())->lockForUpdate()->firstOrFail();

            if (! in_array($locked->verification_status, [VendorVerificationStatus::Pending, VendorVerificationStatus::UnderReview], true)
                || $locked->status !== VendorStatus::Inactive) {
                throw new DomainException(__('Only pending or under-review inactive vendors can be approved.'));
            }

            $previousVerification = $locked->verification_status?->value;
            $previousStatus = $locked->status?->value;

            $locked->forceFill([
                'verification_status' => VendorVerificationStatus::Approved,
                'status' => VendorStatus::Active,
                'approved_at' => now(),
                'approved_by' => $admin->getKey(),
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
            ])->save();

            $this->recordHistory($locked, $previousVerification, $previousStatus, $admin, __('Approved'));

            return $locked;
        });
    }

    public function reject(Vendor $vendor, User $admin, string $reason): Vendor
    {
        return DB::transaction(function () use ($vendor, $admin, $reason) {
            $locked = Vendor::whereKey($vendor->getKey())->lockForUpdate()->firstOrFail();

            if (in_array($locked->status, [VendorStatus::Suspended, VendorStatus::Blocked], true)
                || $locked->verification_status === VendorVerificationStatus::Suspended
                || $locked->verification_status === VendorVerificationStatus::Rejected) {
                throw new DomainException(__('A suspended, blocked, or already rejected vendor cannot be rejected again.'));
            }

            if (! in_array($locked->verification_status, [VendorVerificationStatus::Pending, VendorVerificationStatus::UnderReview, VendorVerificationStatus::Approved], true)) {
                throw new DomainException(__('This vendor cannot be rejected from its current status.'));
            }

            $previousVerification = $locked->verification_status?->value;
            $previousStatus = $locked->status?->value;

            $locked->forceFill([
                'verification_status' => VendorVerificationStatus::Rejected,
                'status' => VendorStatus::Inactive,
                'approved_at' => null,
                'approved_by' => null,
                'rejected_at' => now(),
                'rejected_by' => $admin->getKey(),
                'rejection_reason' => $reason,
            ])->save();

            $locked->products()->update(['is_active' => false]);
            $this->recordHistory($locked, $previousVerification, $previousStatus, $admin, $reason);

            return $locked;
        });
    }

    public function suspend(Vendor $vendor, User $admin, string $reason): Vendor
    {
        return DB::transaction(function () use ($vendor, $admin, $reason) {
            $locked = Vendor::whereKey($vendor->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->verification_status !== VendorVerificationStatus::Approved
                || $locked->status !== VendorStatus::Active) {
                throw new DomainException(__('Only active approved vendors can be suspended.'));
            }

            $previousVerification = $locked->verification_status?->value;
            $previousStatus = $locked->status?->value;

            $locked->forceFill([
                'verification_status' => VendorVerificationStatus::Suspended,
                'status' => VendorStatus::Suspended,
                'approved_at' => null,
                'approved_by' => null,
                'rejected_at' => null,
                'rejected_by' => null,
                'rejection_reason' => null,
            ])->save();

            $locked->products()->update(['is_active' => false]);
            $this->recordHistory($locked, $previousVerification, $previousStatus, $admin, $reason);

            return $locked;
        });
    }

    private function recordHistory(Vendor $vendor, ?string $previousVerification, ?string $previousStatus, User $admin, string $reason): void
    {
        VendorStatusHistory::create([
            'vendor_id' => $vendor->getKey(),
            'previous_verification_status' => $previousVerification,
            'new_verification_status' => $vendor->verification_status?->value,
            'previous_account_status' => $previousStatus,
            'new_account_status' => $vendor->status?->value,
            'changed_by' => $admin->getKey(),
            'reason' => $reason,
        ]);
    }
}
