<?php

namespace App\Modules\Identity\Contracts;

use App\Models\User;

/**
 * Integration point for phone verification.
 *
 * No SMS provider is configured yet. Bind a real implementation in
 * IdentityServiceProvider once a provider is chosen. Implementations must
 * never store plaintext verification codes.
 */
interface PhoneVerificationGateway
{
    public function sendCode(User $user): void;

    public function verify(User $user, string $code): bool;
}
