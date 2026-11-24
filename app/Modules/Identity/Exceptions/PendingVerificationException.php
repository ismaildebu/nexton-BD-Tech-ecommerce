<?php

namespace App\Modules\Identity\Exceptions;

use App\Models\User;
use RuntimeException;

class PendingVerificationException extends RuntimeException
{
    public function __construct(public readonly User $user)
    {
        parent::__construct('The account must be verified before signing in.');
    }
}
