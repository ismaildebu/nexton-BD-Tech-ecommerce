@extends('layouts.guest')

@section('title', __('Reset password'))
@section('heading', __('Reset password'))

@section('content')
    <p class="mb-4 text-sm text-gray-600">{{ __('Enter your account email. We will send a link to choose a new password.') }}</p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate>
        @csrf

        <x-form.input name="email" type="email" :label="__('Email')" autocomplete="email" required />

        <button type="submit" class="w-full rounded-md bg-teal-700 px-4 py-2 font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-700/40">
            {{ __('Send reset link') }}
        </button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-teal-800 hover:underline">{{ __('Back to sign in') }}</a>
        </p>
    </form>
@endsection
