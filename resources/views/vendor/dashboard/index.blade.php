@extends('layouts.vendor')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Vendor Dashboard') }}</h1>
    @if ($vendor)
        <p>{{ $vendor->business_name }} ({{ $vendor->verification_status->label() }})</p>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div class="bg-white rounded border p-4"><p class="text-sm text-gray-500">Products</p><p class="text-2xl font-bold">{{ $stats['products'] ?? 0 }}</p></div>
            <div class="bg-white rounded border p-4"><p class="text-sm text-gray-500">Active</p><p class="text-2xl font-bold">{{ $stats['activeProducts'] ?? 0 }}</p></div>
        </div>
    @else
        <p>{{ __('No vendor profile yet.') }} <a href="{{ route('vendor.register.create') }}" class="underline">{{ __('Register') }}</a></p>
    @endif
@endsection