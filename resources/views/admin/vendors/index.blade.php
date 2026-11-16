@extends('layouts.admin')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('Vendors') }}</h1>
        <ul>
            @foreach ($vendors as $vendor)
                <li class="mb-2">
                    <a href="{{ route('admin.vendors.show', $vendor) }}" class="underline">{{ $vendor->business_name }}</a>
                    <span class="text-sm text-gray-600">{{ $vendor->verification_status->label() }}</span>
                </li>
            @endforeach
        </ul>
        {{ $vendors->links() }}
    </div>
@endsection
