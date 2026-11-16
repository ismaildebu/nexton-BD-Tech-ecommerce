<?php

namespace App\Modules\Identity\Providers;

use App\Models\Address;
use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use App\Modules\Identity\Policies\AddressPolicy;
use App\Modules\Identity\Policies\UserPolicy;
use App\Modules\Identity\Support\NullPhoneVerificationGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Replace this binding once an SMS provider is configured.
        $this->app->bind(PhoneVerificationGateway::class, NullPhoneVerificationGateway::class);
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Address::class, AddressPolicy::class);

        Password::defaults(fn () => Password::min(8)->letters()->mixedCase()->numbers());
    }
}
