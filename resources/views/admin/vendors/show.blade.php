@extends('layouts.admin')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ $vendor->business_name }}</h1>
        <p>{{ __('Status:') }} {{ $vendor->verification_status->label() }}</p>
        <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}" class="inline">
            @csrf
            <button type="submit" class="bg-green-700 text-white px-3 py-1 rounded">{{ __('Approve') }}</button>
        </form>
        <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}" class="inline">
            @csrf
            <input type="text" name="reason" placeholder="{{ __('Reason') }}" required class="border rounded px-2 py-1">
            <button type="submit" class="bg-red-700 text-white px-3 py-1 rounded">{{ __('Reject') }}</button>
        </form>
    </div>
@endsection
