@extends('layouts.guest')

@section('title', __('Reset password'))
@section('heading', __('Forgot your password?'))

@section('content')
    <div class="mb-6 flex items-start gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7a4.5 4.5 0 00-9 0v3.5M6 10.5h12a1.5 1.5 0 011.5 1.5v7a1.5 1.5 0 01-1.5 1.5H6a1.5 1.5 0 01-1.5-1.5v-7A1.5 1.5 0 016 10.5zM12 14v2.5"/></svg>
        </span>
        <p class="pt-1 text-sm leading-6 text-slate-500">{{ __('Enter your account email or verified mobile number. We will send a reset link or code to choose a new password.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate
        x-data="{ pending: false }" @submit="pending = true">
        @csrf

        <x-form.input name="login" :label="__('Email or mobile number')" autocomplete="username" required
            placeholder="you@example.com or 01XXXXXXXXX" />

        <x-ui.button type="submit" class="w-full" :disabled="false" x-bind:disabled="pending" :aria-busy="false"
            x-bind:aria-busy="pending ? 'true' : 'false'">
            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
            <span x-text="pending ? @js(__('Sending...')) : @js(__('Send reset instructions'))">{{ __('Send reset instructions') }}</span>
        </x-ui.button>
    </form>

    <div class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-center text-sm">
        <p><a href="{{ route('password.phone') }}" class="ui-link">{{ __('Reset using a phone code') }}</a></p>
        <p><a href="{{ route('login') }}" class="font-semibold text-slate-500 hover:text-slate-900">{{ __('Back to sign in') }}</a></p>
    </div>
@endsection
