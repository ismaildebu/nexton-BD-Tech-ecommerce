@extends('layouts.app')

@section('title', __('Profile'))

@section('content')
    <x-ui.page-header :title="__('Your profile')" :description="__('Keep your contact details and preferences up to date.')"
        :eyebrow="__('Account settings')">
        <x-slot:action>
            <x-ui.button :href="route('customer.account.index')" variant="outline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5l-7.5-7.5 7.5-7.5M3 12h18"/></svg>
                {{ __('Account overview') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    <nav class="ui-card mb-7 overflow-hidden p-2" aria-label="{{ __('Account sections') }}">
        <div class="ui-scrollbar flex gap-1 overflow-x-auto">
            @foreach ([
                ['customer.account.index', __('Overview')],
                ['orders.index', __('Orders')],
                ['addresses.index', __('Addresses')],
                ['payments.index', __('Payments')],
                ['shipping.index', __('Shipping')],
                ['reviews.index', __('Reviews')],
            ] as [$routeName, $label])
                <a href="{{ route($routeName) }}" @if(request()->routeIs($routeName)) aria-current="page" @endif
                    class="shrink-0 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 {{ request()->routeIs($routeName) ? 'bg-ink-900 text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">{{ $label }}</a>
            @endforeach
        </div>
    </nav>

    <div class="grid items-start gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <x-ui.card>
            <div class="mb-6 flex items-start gap-3 border-b border-slate-100 pb-5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 1116 0"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Personal information') }}</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('This information helps us personalize your account.') }}</p>
                </div>
            </div>

            @if (($user->email !== null && ! $user->hasVerifiedEmail()) || ($user->phone !== null && ! $user->hasVerifiedPhone()))
                <x-ui.alert tone="warning" class="mb-6">
                    {{ __('One or more contacts need verification.') }}
                    <a href="{{ route('verification.notice') }}" class="ml-1 font-bold underline underline-offset-2">{{ __('Verify now') }}</a>
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5" novalidate
                x-data="{ pending: false }" @submit="pending = true">
                @csrf
                @method('PATCH')

                <x-form.input name="name" :label="__('Full name')" :value="$user->name" autocomplete="name" required />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="email" type="email" :label="__('Email')" :value="$user->email" autocomplete="email" />
                    <x-form.input name="phone" type="tel" :label="__('Mobile number')" :value="$user->phone" autocomplete="tel"
                        placeholder="01XXXXXXXXX" :hint="__('Changing your email or mobile number marks it as unverified.')" />
                </div>

                <div class="border-t border-slate-100 pt-5">
                    <h3 class="mb-4 text-sm font-bold text-slate-900">{{ __('Preferences') }}</h3>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.select name="preferred_language" :label="__('Preferred language')">
                            <option value="">{{ __('Not set') }}</option>
                            <option value="bn" @selected(old('preferred_language', $profile?->preferred_language) === 'bn')>বাংলা</option>
                            <option value="en" @selected(old('preferred_language', $profile?->preferred_language) === 'en')>English</option>
                        </x-form.select>
                        <x-form.input name="date_of_birth" type="date" :label="__('Date of birth')"
                            :value="$profile?->date_of_birth?->format('Y-m-d')" :max="today()->format('Y-m-d')" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
                    <x-ui.button type="submit" :disabled="false" x-bind:disabled="pending" :aria-busy="false"
                        x-bind:aria-busy="pending ? 'true' : 'false'">
                        <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
                        <span x-text="pending ? @js(__('Saving...')) : @js(__('Save changes'))">{{ __('Save changes') }}</span>
                    </x-ui.button>
                    <x-ui.button :href="route('customer.account.index')" variant="ghost">{{ __('Cancel') }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-6 flex items-start gap-3 border-b border-slate-100 pb-5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7a4.5 4.5 0 00-9 0v3.5M6 10.5h12a1.5 1.5 0 011.5 1.5v7a1.5 1.5 0 01-1.5 1.5H6a1.5 1.5 0 01-1.5-1.5v-7A1.5 1.5 0 016 10.5zM12 14v2.5"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Change password') }}</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('Use a unique password to keep your account secure.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-5" novalidate
                x-data="{ pending: false }" @submit="pending = true">
                @csrf
                @method('PUT')

                <x-form.input name="current_password" type="password" :label="__('Current password')" autocomplete="current-password" required />
                <x-form.input name="password" type="password" :label="__('New password')" autocomplete="new-password" required
                    :hint="__('At least 8 characters with upper case, lower case and a number.')" />
                <x-form.input name="password_confirmation" type="password" :label="__('Confirm new password')" autocomplete="new-password" required />

                <x-ui.button type="submit" variant="dark" class="w-full" :disabled="false" x-bind:disabled="pending">
                    <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
                    <span x-text="pending ? @js(__('Updating...')) : @js(__('Update password'))">{{ __('Update password') }}</span>
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>
@endsection
