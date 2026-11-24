<?php

namespace App\Modules\Identity\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case PendingVerification = 'pending_verification';

    public function canAuthenticate(): bool
    {
        return $this === self::Active;
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
