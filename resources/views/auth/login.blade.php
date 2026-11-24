@extends('layouts.guest')

@section('title', __('Sign in'))
@section('heading', __('Welcome back'))

@section('content')
    <div class="mb-6 flex items-start gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 1115 0v.25h-15v-.25z"/></svg>
        </span>
        <p class="pt-1 text-sm leading-6 text-slate-500">{{ __('Sign in to manage your orders, addresses and account details.') }}</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" novalidate
        x-data="{ show: false, pending: false }" @submit="pending = true">
        @csrf

        <x-form.input name="login" :label="__('Email or mobile number')" autocomplete="username" required
            placeholder="you@example.com or 01XXXXXXXXX" />

        <div class="relative">
            <x-form.input name="password" type="password" :label="__('Password')" autocomplete="current-password" required
                class="pr-20" x-bind:type="show ? 'text' : 'password'" />
            <button type="button" x-on:click="show = !show" :aria-pressed="show.toString()" aria-controls="password"
                class="absolute right-1 top-[27px] flex h-11 items-center rounded-lg px-3 text-xs font-bold text-slate-500 transition hover:bg-slate-50 hover:text-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20"
                x-text="show ? @js(__('Hide')) : @js(__('Show'))">{{ __('Show') }}</button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-form.checkbox name="remember" :label="__('Keep me signed in')" />
            <a href="{{ route('password.request') }}" class="ui-link text-sm">{{ __('Forgot password?') }}</a>
        </div>

        <x-ui.button type="submit" class="w-full" :disabled="false" x-bind:disabled="pending" :aria-busy="false"
            x-bind:aria-busy="pending ? 'true' : 'false'">
            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
            <span x-text="pending ? @js(__('Signing in...')) : @js(__('Sign in'))">{{ __('Sign in') }}</span>
        </x-ui.button>
    </form>

    <div class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
        {{ __('New here?') }}
        <a href="{{ route('register') }}" class="ui-link ml-1">{{ __('Create an account') }}</a>
    </div>
@endsection
