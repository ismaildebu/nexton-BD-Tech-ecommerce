<?php

namespace App\Modules\Identity\Services;

use App\Models\User;
use App\Modules\Identity\Services\VerificationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserProfileService
{
    public function __construct(private readonly VerificationService $verification) {}

    /**
     * Update the signed-in user's own profile.
     *
     * Changing the email or phone clears the matching verification timestamp.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $emailChanged = false;
        $phoneChanged = false;

        $updated = DB::transaction(function () use ($user, $data, &$emailChanged, &$phoneChanged): User {
            $locked = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $locked->fill(Arr::only($data, ['name', 'email', 'phone']));

            $emailChanged = $locked->isDirty('email');
            $phoneChanged = $locked->isDirty('phone');

            if ($emailChanged) {
                $locked->forceFill(['email_verified_at' => null]);
            }

            if ($phoneChanged) {
                $locked->forceFill(['phone_verified_at' => null]);
            }

            $this->verification->syncStatus($locked);
            $locked->save();

            $profileData = Arr::only($data, ['preferred_language', 'date_of_birth']);

            if ($profileData !== []) {
                $profile = $locked->customerProfile()->firstOrNew();
                $profile->fill($profileData);
                $locked->customerProfile()->save($profile);
            }

            return $locked->refresh();
        });

        if ($emailChanged) {
            $this->verification->sendEmail($updated);
        }

        if ($phoneChanged) {
            $this->verification->sendPhone($updated);
        }

        return $updated;
    }

    /**
     * Change the password after verifying the current one.
     *
     * Other database-backed sessions of this user are removed.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword, ?string $currentSessionId = null): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The current password is incorrect.'),
            ]);
        }

        DB::transaction(function () use ($user, $newPassword, $currentSessionId): void {
            $user->forceFill([
                'password' => $newPassword,
                'remember_token' => Str::random(60),
            ])->save();

            if (config('session.driver') === 'database' && $currentSessionId !== null) {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->getKey())
                    ->where('id', '!=', $currentSessionId)
                    ->delete();
            }
        });
    }
}
