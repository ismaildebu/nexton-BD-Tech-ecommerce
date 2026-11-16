<?php

namespace App\Modules\Identity\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case PendingVerification = 'pending_verification';

    /**
     * Only active and pending-verification accounts may sign in.
     */
    public function canAuthenticate(): bool
    {
        return match ($this) {
            self::Active, self::PendingVerification => true,
            self::Inactive, self::Suspended => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::Suspended => __('Suspended'),
            self::PendingVerification => __('Pending verification'),
        };
    }
}
