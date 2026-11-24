@extends('layouts.guest')

@section('title', __('Reset password with mobile'))
@section('heading', __('Reset with your phone'))

@section('content')
    <p class="mb-6 text-sm leading-6 text-slate-500">{{ __('Enter your verified mobile number, the six-digit code, and a new password.') }}</p>

    <form method="POST" action="{{ route('password.phone.update') }}" class="space-y-5" novalidate
        x-data="{ pending: false }" @submit="pending = true">
        @csrf

        <x-form.input name="phone" type="tel" :label="__('Mobile number')" autocomplete="tel" placeholder="01XXXXXXXXX" required />
        <x-form.input name="code" :label="__('Verification code')" autocomplete="one-time-code" inputmode="numeric"
            pattern="[0-9]{6}" maxlength="6" placeholder="000000" :hint="__('Enter the 6-digit code sent to your phone.')" required />
        <x-form.input name="password" type="password" :label="__('New password')" autocomplete="new-password" required
            :hint="__('At least 8 characters with upper case, lower case and a number.')" />
        <x-form.input name="password_confirmation" type="password" :label="__('Confirm new password')" autocomplete="new-password" required />

        <x-ui.button type="submit" class="w-full" :disabled="false" x-bind:disabled="pending" :aria-busy="false"
            x-bind:aria-busy="pending ? 'true' : 'false'">
            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
            <span x-text="pending ? @js(__('Resetting...')) : @js(__('Reset password'))">{{ __('Reset password') }}</span>
        </x-ui.button>
    </form>

    <div class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-center text-sm">
        <p><a href="{{ route('password.request') }}" class="ui-link">{{ __('Request a new code') }}</a></p>
        <p><a href="{{ route('login') }}" class="font-semibold text-slate-500 hover:text-slate-900">{{ __('Back to sign in') }}</a></p>
    </div>
@endsection
