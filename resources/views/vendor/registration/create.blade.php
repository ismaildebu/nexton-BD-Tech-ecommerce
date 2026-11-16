@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('Vendor Registration') }}</h1>
        <form method="POST" action="{{ route('vendor.register.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium">{{ __('Business name') }}</label>
                <input type="text" name="business_name" class="border rounded px-3 py-2 w-full" required>
            </div>
            <button type="submit" class="bg-teal-700 text-white px-4 py-2 rounded">{{ __('Submit') }}</button>
        </form>
    </div>
@endsection