@extends('layouts.guest')

@section('title', __('Verify mobile number'))
@section('heading', __('Check your phone'))

@section('content')
    <div class="mb-6 flex items-start gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 3h3a1.5 1.5 0 011.5 1.5v15a1.5 1.5 0 01-1.5 1.5h-3A1.5 1.5 0 019 19.5v-15A1.5 1.5 0 0110.5 3zM8 6.5h8M8 17.5h8"/></svg>
        </span>
        <p class="pt-1 text-sm leading-6 text-slate-500">
            {{ __('Enter the six-digit code sent to :phone.', ['phone' => $user->phone]) }}
        </p>
    </div>

    <form method="POST" action="{{ route('verification.phone.store') }}" class="space-y-5" novalidate
        x-data="{ pending: false }" @submit="pending = true">
        @csrf

        <x-form.input name="code" :label="__('Verification code')" autocomplete="one-time-code" inputmode="numeric"
            pattern="[0-9]{6}" maxlength="6" placeholder="000000" :hint="__('The code is valid for a limited time.')" required
            class="text-center text-lg font-bold tracking-[0.35em]" />

        <x-ui.button type="submit" class="w-full" :disabled="false" x-bind:disabled="pending" :aria-busy="false"
            x-bind:aria-busy="pending ? 'true' : 'false'">
            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
            <span x-text="pending ? @js(__('Verifying...')) : @js(__('Verify mobile number'))">{{ __('Verify mobile number') }}</span>
        </x-ui.button>
    </form>

    <form method="POST" action="{{ route('verification.resend') }}" class="mt-4"
        x-data="{ pending: false }" @submit="pending = true">
        @csrf
        <input type="hidden" name="channel" value="phone">
        <x-ui.button type="submit" variant="outline" class="w-full" :disabled="false" x-bind:disabled="pending">
            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
            <span x-text="pending ? @js(__('Sending...')) : @js(__('Resend code'))">{{ __('Resend code') }}</span>
        </x-ui.button>
    </form>

    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm">
        <a href="{{ route('verification.notice') }}" class="ui-link">{{ __('Back to verification') }}</a>
    </p>
@endsection
