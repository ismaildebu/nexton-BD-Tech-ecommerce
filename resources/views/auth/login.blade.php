@extends('layouts.guest')

@section('title', __('Sign in'))
@section('heading', __('Sign in'))

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="space-y-4" novalidate x-data="{ show: false }">
        @csrf

        <x-form.input name="login" :label="__('Email or mobile number')" autocomplete="username" required />

        <div>
            <label for="password" class="block text-sm font-medium text-gray-800">{{ __('Password') }}</label>
            <div class="relative mt-1">
                <input id="password" name="password" required autocomplete="current-password"
                    x-bind:type="show ? 'text' : 'password'" type="password"
                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 pr-16 text-gray-900 focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-700/30">
                <button type="button" x-on:click="show = !show"
                    class="absolute inset-y-0 right-0 px-3 text-sm text-gray-600 hover:text-teal-800"
                    x-text="show ? '{{ __('Hide') }}' : '{{ __('Show') }}'">{{ __('Show') }}</button>
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-teal-700 focus:ring-teal-700">
                {{ __('Keep me signed in') }}
            </label>
            <a href="{{ route('password.request') }}" class="text-teal-800 hover:underline">{{ __('Forgot password?') }}</a>
        </div>

        <button type="submit" class="w-full rounded-md bg-teal-700 px-4 py-2 font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-700/40">
            {{ __('Sign in') }}
        </button>

        <p class="text-center text-sm text-gray-600">
            {{ __('New here?') }}
            <a href="{{ route('register') }}" class="font-medium text-teal-800 hover:underline">{{ __('Create account') }}</a>
        </p>
    </form>
@endsection
