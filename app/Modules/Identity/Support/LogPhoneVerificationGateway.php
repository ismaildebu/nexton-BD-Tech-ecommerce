<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use LogicException;

final class LogPhoneVerificationGateway implements PhoneVerificationGateway
{
    public function sendCode(User $user, string $purpose): void
    {
        if (app()->environment('production')) {
            throw new LogicException('The log phone verification driver is disabled in production.');
        }

        $this->assertPurpose($purpose);

        if ($user->phone === null) {
            throw new LogicException('A phone number is required to send a verification code.');
        }

        $key = $this->key($user, $purpose);
        $ttl = (int) config('services.phone_verification.ttl');
        $cooldown = (int) config('services.phone_verification.cooldown');

        Cache::lock($key.':lock', 10)->block(5, function () use ($user, $purpose, $key, $ttl, $cooldown): void {
            $existing = Cache::get($key);
            $now = now();

            if (is_array($existing)
                && $existing['expires_at'] > $now->timestamp
                && $existing['last_sent_at'] > $now->timestamp - $cooldown) {
                throw ValidationException::withMessages([
                    'code' => __('Please wait before requesting another code.'),
                ]);
            }

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            Cache::put($key, [
                'phone' => $user->phone,
                'code_hash' => Hash::make($code),
                'expires_at' => $now->copy()->addSeconds($ttl)->timestamp,
                'last_sent_at' => $now->timestamp,
                'attempts' => 0,
            ], $ttl);

            Log::info('Phone verification code', [
                'phone' => $user->phone,
                'purpose' => $purpose,
                'code' => $code,
            ]);
        });
    }

    public function verify(User $user, string $code, string $purpose): bool
    {
        $this->assertPurpose($purpose);

        if ($user->phone === null) {
            return false;
        }

        $key = $this->key($user, $purpose);
        $maxAttempts = (int) config('services.phone_verification.max_attempts');

        return Cache::lock($key.':lock', 10)->block(5, function () use ($user, $key, $code, $maxAttempts): bool {
            $challenge = Cache::get($key);

            if (! is_array($challenge)
                || $challenge['phone'] !== $user->phone
                || $challenge['expires_at'] <= now()->timestamp
                || $challenge['attempts'] >= $maxAttempts) {
                Cache::forget($key);

                return false;
            }

            if (Hash::check($code, $challenge['code_hash'])) {
                Cache::forget($key);

                return true;
            }

            $attempts = $challenge['attempts'] + 1;

            if ($attempts >= $maxAttempts) {
                Cache::forget($key);
            } else {
                $ttl = max(1, $challenge['expires_at'] - now()->timestamp);
                Cache::put($key, array_merge($challenge, ['attempts' => $attempts]), $ttl);
            }

            return false;
        });
    }

    public function invalidate(User $user, string $purpose): void
    {
        $this->assertPurpose($purpose);

        if ($user->phone === null) {
            return;
        }

        $key = $this->key($user, $purpose);

        Cache::lock($key.':lock', 10)->block(5, function () use ($key): void {
            Cache::forget($key);
        });
    }

    private function key(User $user, string $purpose): string
    {
        return 'phone-verification:'.$purpose.':'.$user->getKey().':'.sha1((string) $user->phone);
    }

    private function assertPurpose(string $purpose): void
    {
        if (! in_array($purpose, [self::PURPOSE_VERIFICATION, self::PURPOSE_PASSWORD_RESET], true)) {
            throw new LogicException('Unsupported phone verification purpose.');
        }
    }
}
