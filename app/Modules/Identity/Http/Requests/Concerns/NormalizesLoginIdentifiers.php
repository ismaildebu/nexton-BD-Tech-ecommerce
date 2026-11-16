<?php

namespace App\Modules\Identity\Http\Requests\Concerns;

use App\Modules\Identity\Support\PhoneNumber;
use Illuminate\Support\Str;

trait NormalizesLoginIdentifiers
{
    /**
     * Lowercase the email, convert the phone to +8801XXXXXXXXX and turn empty values into null.
     * An unrecognised phone is kept as typed so validation can reject it.
     */
    protected function normalizeLoginIdentifiers(): void
    {
        $email = $this->input('email');
        $phone = $this->input('phone');

        if (is_string($email)) {
            $email = Str::lower(trim($email));
            $email = $email === '' ? null : $email;
        }

        if (is_string($phone)) {
            $trimmed = trim($phone);
            $phone = $trimmed === '' ? null : (PhoneNumber::normalize($trimmed) ?? $trimmed);
        }

        $this->merge(['email' => $email, 'phone' => $phone]);
    }
}
