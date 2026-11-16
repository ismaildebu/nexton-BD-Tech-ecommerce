@extends('layouts.guest')

@section('title', __('Create account'))
@section('heading', __('Create account'))

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="space-y-4" novalidate>
        @csrf

        <x-form.input name="name" :label="__('Full name')" autocomplete="name" required />
        <x-form.input name="email" type="email" :label="__('Email')" autocomplete="email" />
        <x-form.input name="phone" type="tel" :label="__('Mobile number')" autocomplete="tel" placeholder="01XXXXXXXXX"
            :hint="__('Enter an email address, a mobile number, or both.')" />
        <x-form.input name="password" type="password" :label="__('Password')" autocomplete="new-password" required
            :hint="__('At least 8 characters with upper case, lower case and a number.')" />
        <x-form.input name="password_confirmation" type="password" :label="__('Confirm password')" autocomplete="new-password" required />

        <button type="submit" class="w-full rounded-md bg-teal-700 px-4 py-2 font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-700/40">
            {{ __('Create account') }}
        </button>

        <p class="text-center text-sm text-gray-600">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="font-medium text-teal-800 hover:underline">{{ __('Sign in') }}</a>
        </p>
    </form>
@endsection
