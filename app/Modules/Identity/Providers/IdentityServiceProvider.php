<?php

namespace App\Modules\Identity\Providers;

use App\Models\Address;
use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use App\Modules\Identity\Policies\AddressPolicy;
use App\Modules\Identity\Policies\UserPolicy;
use App\Modules\Identity\Support\LogPhoneVerificationGateway;
use App\Modules\Identity\Support\NullPhoneVerificationGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $driver = config('services.phone_verification.driver', 'log');
        $implementation = $driver === 'log' ? LogPhoneVerificationGateway::class : NullPhoneVerificationGateway::class;

        $this->app->bind(PhoneVerificationGateway::class, $implementation);
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Address::class, AddressPolicy::class);

        Password::defaults(fn () => Password::min(8)->letters()->mixedCase()->numbers());
    }
}
