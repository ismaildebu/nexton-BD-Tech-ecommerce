<?php

namespace App\Modules\Vendor\Enums;

enum VendorVerificationStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Pending => __('Pending'),
            self::UnderReview => __('Under review'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::Suspended => __('Suspended'),
        };
    }
}
