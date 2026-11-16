<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use LogicException;

/**
 * Placeholder used until an SMS provider is configured.
 */
final class NullPhoneVerificationGateway implements PhoneVerificationGateway
{
    public function sendCode(User $user): void
    {
        throw new LogicException('Phone verification provider is not configured.');
    }

    public function verify(User $user, string $code): bool
    {
        throw new LogicException('Phone verification provider is not configured.');
    }
}
