<?php

namespace App\Modules\Identity\Services;

use App\Models\CustomerProfile;
use App\Models\User;
use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Identity\Support\PhoneNumber;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 60;

    /**
     * A valid bcrypt-formatted hash that matches no real password. It is checked when
     * no user exists, so response time does not reveal whether an account exists.
     */
    private const DUMMY_HASH = '$2y$12$abcdefghijklmnopqrstuuK5yQ1bYV2o0Zp3mJt9nXr6sLwHqEoVi';

    /**
     * Register a new user together with an empty customer profile.
     *
     * @param  array{name: string, email?: ?string, phone?: ?string, password: string}  $data
     */
    public function register(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $user = new User(Arr::only($data, ['name', 'email', 'phone', 'password']));
            $user->forceFill(['status' => UserStatus::PendingVerification]);
            $user->save();

            $user->customerProfile()->save(new CustomerProfile);

            return $user;
        });

        event(new Registered($user));

        return $user;
    }

    /**
     * Resolve and verify credentials. Accepts an email address or a phone number.
     *
     * @throws ValidationException
     */
    public function authenticate(string $login, string $password, string $ip): User
    {
        $login = trim($login);
        $throttleKey = 'login:'.sha1(Str::lower($login)).'|'.$ip;

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }

        $user = $this->findByLogin($login);

        // Always run one hash check so timing does not reveal whether the account exists.
        $passwordMatches = Hash::check($password, $user?->password ?? self::DUMMY_HASH);

        if ($user === null || ! $passwordMatches) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

            throw ValidationException::withMessages([
                'login' => __('These credentials do not match our records.'),
            ]);
        }

        // Status is revealed only after the correct password was supplied.
        if (! $user->canAuthenticate()) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

            throw ValidationException::withMessages([
                'login' => __('This account cannot sign in. Please contact support.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        return $user;
    }

    /**
     * Sign the user in, regenerate the session and record the login time.
     */
    public function login(Request $request, User $user, bool $remember = false): void
    {
        Auth::guard('web')->login($user, $remember);

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Send a reset link. The caller must show the same message whether or not
     * the email exists, to prevent account enumeration.
     */
    public function sendPasswordResetLink(string $email): void
    {
        Password::broker()->sendResetLink(['email' => Str::lower(trim($email))]);
    }

    /**
     * @param  array{email: string, password: string, password_confirmation: string, token: string}  $credentials
     * @return bool True when the password was reset.
     */
    public function resetPassword(array $credentials): bool
    {
        $credentials['email'] = Str::lower(trim($credentials['email']));

        $status = Password::broker()->reset(
            Arr::only($credentials, ['email', 'password', 'password_confirmation', 'token']),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET;
    }

    private function findByLogin(string $login): ?User
    {
        if ($login === '') {
            return null;
        }

        if (str_contains($login, '@')) {
            return User::query()->where('email', Str::lower($login))->first();
        }

        $phone = PhoneNumber::normalize($login);

        return $phone === null ? null : User::query()->where('phone', $phone)->first();
    }
}
