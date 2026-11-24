<?php

namespace App\Modules\Identity\Contracts;

use App\Models\User;

interface PhoneVerificationGateway
{
    public const PURPOSE_VERIFICATION = 'verification';

    public const PURPOSE_PASSWORD_RESET = 'password_reset';

    public function sendCode(User $user, string $purpose): void;

    public function verify(User $user, string $code, string $purpose): bool;

    public function invalidate(User $user, string $purpose): void;
}
