@extends('layouts.vendor')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Vendor Dashboard') }}</h1>
    @if ($vendor)
        <p>{{ $vendor->business_name }} ({{ $vendor->verification_status->label() }})</p>
    @else
        <p>{{ __('No vendor profile yet.') }} <a href="{{ route('vendor.register.create') }}" class="underline">{{ __('Register') }}</a></p>
    @endif
@endsection