@extends('layouts.guest')

@section('title', __('Choose a new password'))
@section('heading', __('Choose a new password'))

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <x-form.input name="email" type="email" :label="__('Email')" :value="$email" autocomplete="email" required />
        <x-form.input name="password" type="password" :label="__('New password')" autocomplete="new-password" required
            :hint="__('At least 8 characters with upper case, lower case and a number.')" />
        <x-form.input name="password_confirmation" type="password" :label="__('Confirm new password')" autocomplete="new-password" required />

        <button type="submit" class="w-full rounded-md bg-teal-700 px-4 py-2 font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-700/40">
            {{ __('Reset password') }}
        </button>
    </form>
@endsection
