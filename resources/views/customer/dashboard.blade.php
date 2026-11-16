@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('Customer Dashboard') }}</h1>
        <div class="grid gap-4">
            <div class="p-4 border rounded">
                <p class="font-semibold">{{ $user->name }}</p>
                <p>{{ $user->email ?? $user->phone }}</p>
                <p>{{ __('Addresses: :count', ['count' => $addressCount]) }}</p>
                @if ($defaultAddress)
                    <p>{{ __('Default address:') }} {{ $defaultAddress->full_name }}, {{ $defaultAddress->address_line }}</p>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('profile.edit') }}" class="underline">{{ __('Profile') }}</a>
                <a href="{{ route('addresses.index') }}" class="underline">{{ __('Addresses') }}</a>
                <a href="{{ route('customer.account.index') }}" class="underline">{{ __('Account') }}</a>
            </div>
        </div>
    </div>
@endsection
