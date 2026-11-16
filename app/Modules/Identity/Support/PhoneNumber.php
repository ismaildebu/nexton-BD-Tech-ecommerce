<?php

namespace App\Modules\Identity\Support;

/**
 * Normalizes Bangladeshi mobile numbers to E.164 (+8801XXXXXXXXX).
 */
final class PhoneNumber
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Accept Bengali digits.
        $value = strtr(trim($value), [
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
            '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        ]);

        $digits = preg_replace('/[\s\-\(\)\.]/', '', $value);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '+880')) {
            $digits = '0'.substr($digits, 4);
        } elseif (str_starts_with($digits, '880') && strlen($digits) === 13) {
            $digits = '0'.substr($digits, 3);
        }

        if (! preg_match('/^01[3-9]\d{8}$/', $digits)) {
            return null;
        }

        return '+88'.$digits;
    }
}
