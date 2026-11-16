@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('Account') }}</h1>
        <div class="p-4 border rounded">
            <p><strong>{{ __('Name') }}:</strong> {{ $user->name }}</p>
            <p><strong>{{ __('Email') }}:</strong> {{ $user->email ?? '-' }}</p>
            <p><strong>{{ __('Phone') }}:</strong> {{ $user->phone ?? '-' }}</p>
            <p><strong>{{ __('Status') }}:</strong> {{ $user->status->label() }}</p>
            <p><strong>{{ __('Email verified') }}:</strong> {{ $user->email_verified_at ? __('Yes') : __('No') }}</p>
            <p><strong>{{ __('Phone verified') }}:</strong> {{ $user->phone_verified_at ? __('Yes') : __('No') }}</p>
        </div>
    </div>
@endsection