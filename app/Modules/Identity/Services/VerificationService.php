<?php

namespace App\Modules\Identity\Services;

use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use App\Modules\Identity\Enums\UserStatus;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    public function __construct(private readonly PhoneVerificationGateway $phoneVerification) {}

    public function sendInitialChallenges(User $user): void
    {
        $this->sendEmail($user);
        $this->sendPhone($user);
    }

    public function sendEmail(User $user): void
    {
        if ($user->email !== null && ! $user->hasVerifiedEmail()) {
            $user->notify(new VerifyEmail);
        }
    }

    public function sendPhone(User $user): void
    {
        if ($user->phone !== null && ! $user->hasVerifiedPhone()) {
            $this->phoneVerification->sendCode($user, PhoneVerificationGateway::PURPOSE_VERIFICATION);
        }
    }

    public function verifyEmail(User $user, string $hash): bool
    {
        return DB::transaction(function () use ($user, $hash): bool {
            $locked = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->email === null || ! hash_equals(sha1($locked->email), $hash)) {
                return false;
            }

            $alreadyVerified = $locked->hasVerifiedEmail();

            if (! $alreadyVerified) {
                $locked->markEmailAsVerified();
            }

            $this->activateIfVerified($locked);

            if (! $alreadyVerified) {
                event(new Verified($locked));
            }

            return true;
        });
    }

    public function verifyPhone(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code): bool {
            $locked = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if (! $this->phoneVerification->verify($locked, $code, PhoneVerificationGateway::PURPOSE_VERIFICATION)) {
                return false;
            }

            $locked->forceFill(['phone_verified_at' => now()])->save();
            $this->activateIfVerified($locked);

            return true;
        });
    }

    public function syncStatus(User $user): void
    {
        if ($user->status === UserStatus::Active && ! $user->hasVerifiedContact()) {
            $user->forceFill(['status' => UserStatus::PendingVerification])->save();
        } elseif ($user->status === UserStatus::PendingVerification && $user->hasVerifiedContact()) {
            $user->forceFill(['status' => UserStatus::Active])->save();
        }
    }

    private function activateIfVerified(User $user): void
    {
        if ($user->status === UserStatus::PendingVerification && $user->hasVerifiedContact()) {
            $user->forceFill(['status' => UserStatus::Active])->save();
        }
    }
}
