<?php

namespace App\Modules\Identity\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserProfileService
{
    /**
     * Update the signed-in user's own profile.
     *
     * Changing the email or phone clears the matching verification timestamp.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->fill(Arr::only($data, ['name', 'email', 'phone']));

            if ($user->isDirty('email')) {
                $user->forceFill(['email_verified_at' => null]);
            }

            if ($user->isDirty('phone')) {
                $user->forceFill(['phone_verified_at' => null]);
            }

            $user->save();

            $profileData = Arr::only($data, ['preferred_language', 'date_of_birth']);

            if ($profileData !== []) {
                $profile = $user->customerProfile()->firstOrNew();
                $profile->fill($profileData);
                $user->customerProfile()->save($profile);
            }

            return $user->refresh();
        });
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
