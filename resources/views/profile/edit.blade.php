@extends('layouts.app')

@section('title', __('Profile'))

@section('content')
    <div class="grid gap-8 md:grid-cols-2">
        <section class="rounded-lg border border-stone-200 bg-white p-6">
            <h1 class="mb-4 text-lg font-semibold">{{ __('Profile') }}</h1>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4" novalidate>
                @csrf
                @method('PATCH')

                <x-form.input name="name" :label="__('Full name')" :value="$user->name" autocomplete="name" required />
                <x-form.input name="email" type="email" :label="__('Email')" :value="$user->email" autocomplete="email" />
                <x-form.input name="phone" type="tel" :label="__('Mobile number')" :value="$user->phone" autocomplete="tel"
                    :hint="__('Changing your email or mobile number marks it as unverified.')" />

                <div>
                    <label for="preferred_language" class="block text-sm font-medium text-gray-800">{{ __('Preferred language') }}</label>
                    <select id="preferred_language" name="preferred_language"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-700/30">
                        <option value="">{{ __('Not set') }}</option>
                        <option value="bn" @selected(old('preferred_language', $profile?->preferred_language) === 'bn')>বাংলা</option>
                        <option value="en" @selected(old('preferred_language', $profile?->preferred_language) === 'en')>English</option>
                    </select>
                    @error('preferred_language')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-form.input name="date_of_birth" type="date" :label="__('Date of birth')"
                    :value="$profile?->date_of_birth?->format('Y-m-d')" />

                <button type="submit" class="rounded-md bg-teal-700 px-4 py-2 font-medium text-white hover:bg-teal-800">
                    {{ __('Save changes') }}
                </button>
            </form>
        </section>

        <section class="rounded-lg border border-stone-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold">{{ __('Change password') }}</h2>

            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4" novalidate>
                @csrf
                @method('PUT')

                <x-form.input name="current_password" type="password" :label="__('Current password')" autocomplete="current-password" required />
                <x-form.input name="password" type="password" :label="__('New password')" autocomplete="new-password" required
                    :hint="__('At least 8 characters with upper case, lower case and a number.')" />
                <x-form.input name="password_confirmation" type="password" :label="__('Confirm new password')" autocomplete="new-password" required />

                <button type="submit" class="rounded-md bg-teal-700 px-4 py-2 font-medium text-white hover:bg-teal-800">
                    {{ __('Change password') }}
                </button>
            </form>
        </section>
    </div>
@endsection
