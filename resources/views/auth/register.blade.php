@extends('layouts.guest')

@section('title', __('Create account'))
@section('heading', __('Join NEXTON'))

@section('content')
    <div class="mb-6 flex items-start gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v6m-3-3h6M12 4a4 4 0 100 8 4 4 0 000-8zM3 20.25a9 9 0 0118 0v.25H3v-.25z"/></svg>
        </span>
        <p class="pt-1 text-sm leading-6 text-slate-500">{{ __('Create your free account for faster checkout, order tracking and saved addresses.') }}</p>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-5" novalidate
        x-data="{ pending: false }" @submit="pending = true">
        @csrf

        <x-form.input name="name" :label="__('Full name')" autocomplete="name" placeholder="Your full name" required />
        <x-form.input name="email" type="email" :label="__('Email')" autocomplete="email" placeholder="you@example.com" />
        <x-form.input name="phone" type="tel" :label="__('Mobile number')" autocomplete="tel" placeholder="01XXXXXXXXX"
            :hint="__('Enter an email address, a mobile number, or both.')" />
        <x-form.input name="password" type="password" :label="__('Password')" autocomplete="new-password" required
            :hint="__('At least 8 characters with upper case, lower case and a number.')" />
        <x-form.input name="password_confirmation" type="password" :label="__('Confirm password')" autocomplete="new-password" required />

        <x-ui.button type="submit" class="w-full" :disabled="false" x-bind:disabled="pending" :aria-busy="false"
            x-bind:aria-busy="pending ? 'true' : 'false'">
            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
            <span x-text="pending ? @js(__('Creating account...')) : @js(__('Create account'))">{{ __('Create account') }}</span>
        </x-ui.button>

        <p class="text-center text-xs leading-5 text-slate-500">{{ __('We will ask you to verify your email or mobile number next.') }}</p>
    </form>

    <div class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="ui-link ml-1">{{ __('Sign in') }}</a>
    </div>
@endsection
