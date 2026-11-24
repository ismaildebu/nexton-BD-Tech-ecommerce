@extends('layouts.guest')

@section('title', __('Choose a new password'))
@section('heading', __('Choose a new password'))

@section('content')
    <p class="mb-6 text-sm leading-6 text-slate-500">{{ __('Choose a strong password that you have not used before.') }}</p>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" novalidate
        x-data="{ pending: false }" @submit="pending = true">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form.input name="email" type="email" :label="__('Email')" :value="$email" autocomplete="email" required />
        <x-form.input name="password" type="password" :label="__('New password')" autocomplete="new-password" required
            :hint="__('At least 8 characters with upper case, lower case and a number.')" />
        <x-form.input name="password_confirmation" type="password" :label="__('Confirm new password')" autocomplete="new-password" required />

        <x-ui.button type="submit" class="w-full" :disabled="false" x-bind:disabled="pending" :aria-busy="false"
            x-bind:aria-busy="pending ? 'true' : 'false'">
            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
            <span x-text="pending ? @js(__('Resetting...')) : @js(__('Reset password'))">{{ __('Reset password') }}</span>
        </x-ui.button>
    </form>

    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm">
        <a href="{{ route('login') }}" class="ui-link">{{ __('Back to sign in') }}</a>
    </p>
@endsection
