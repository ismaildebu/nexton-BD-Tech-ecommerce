<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use LogicException;

final class NullPhoneVerificationGateway implements PhoneVerificationGateway
{
    public function sendCode(User $user, string $purpose): void
    {
        throw new LogicException('Phone verification provider is not configured.');
    }

    public function verify(User $user, string $code, string $purpose): bool
    {
        throw new LogicException('Phone verification provider is not configured.');
    }

    public function invalidate(User $user, string $purpose): void {}
}
