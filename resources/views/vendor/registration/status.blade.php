@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('Application Status') }}</h1>
        @if ($vendor)
            <p>{{ __('Vendor code:') }} {{ $vendor->vendor_code }}</p>
            <p>{{ __('Status:') }} {{ $vendor->verification_status->label() }}</p>
        @else
            <p>{{ __('No application found.') }}</p>
        @endif
    </div>
@endsection