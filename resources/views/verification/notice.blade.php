@extends('layouts.guest')

@section('title', __('Verify your account'))
@section('heading', __('One last step'))

@section('content')
    <div class="mb-6 flex items-start gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5M12 3l7.5 3v5.25c0 4.5-3.1 8.34-7.5 9.75-4.4-1.41-7.5-5.25-7.5-9.75V6L12 3z"/></svg>
        </span>
        <p class="pt-1 text-sm leading-6 text-slate-500">{{ __('Verify at least one contact to activate your account. You can sign in after verification.') }}</p>
    </div>

    @if ($user->hasVerifiedContact())
        <x-ui.alert tone="success" class="mb-5">
            <div class="flex items-start gap-2">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ __('Your account has a verified contact.') }}</span>
            </div>
        </x-ui.alert>
    @endif

    <div class="space-y-4">
        @if ($user->email !== null && ! $user->hasVerifiedEmail())
            <x-ui.card :padding="false" class="overflow-hidden">
                <div class="border-l-4 border-brand-400 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-bold text-slate-900">{{ __('Email address') }}</h2>
                        <x-ui.badge tone="warning">{{ __('Not verified') }}</x-ui.badge>
                    </div>
                    <p class="mt-2 break-all text-sm text-slate-600">{{ $user->email }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Check your inbox for a verification link.') }}</p>
                    <form method="POST" action="{{ route('verification.resend') }}" class="mt-4"
                        x-data="{ pending: false }" @submit="pending = true">
                        @csrf
                        <input type="hidden" name="channel" value="email">
                        <x-ui.button type="submit" variant="outline" size="sm" :disabled="false" x-bind:disabled="pending">
                            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
                            <span x-text="pending ? @js(__('Sending...')) : @js(__('Resend email'))">{{ __('Resend email') }}</span>
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.card>
        @endif

        @if ($user->phone !== null && ! $user->hasVerifiedPhone())
            <x-ui.card :padding="false" class="overflow-hidden">
                <div class="border-l-4 border-accent-400 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-bold text-slate-900">{{ __('Mobile number') }}</h2>
                        <x-ui.badge tone="warning">{{ __('Not verified') }}</x-ui.badge>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ $user->phone }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Use the six-digit code sent to your phone.') }}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <x-ui.button :href="route('verification.phone')" size="sm">
                            {{ __('Enter code') }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12l-7.5 7.5M3 12h18"/></svg>
                        </x-ui.button>
                        <form method="POST" action="{{ route('verification.resend') }}"
                            x-data="{ pending: false }" @submit="pending = true">
                            @csrf
                            <input type="hidden" name="channel" value="phone">
                            <x-ui.button type="submit" variant="outline" size="sm" :disabled="false" x-bind:disabled="pending">
                                <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
                                <span x-text="pending ? @js(__('Sending...')) : @js(__('Resend code'))">{{ __('Resend code') }}</span>
                            </x-ui.button>
                        </form>
                    </div>
                </div>
            </x-ui.card>
        @endif
    </div>

    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm">
        <a href="{{ route('login') }}" class="ui-link">{{ __('Back to sign in') }}</a>
    </p>
@endsection
