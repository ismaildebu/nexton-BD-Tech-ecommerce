@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Shipping') }}</h1>
    @if ($shippings->isEmpty())
        <p class="text-gray-500">{{ __('No shipments yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($shippings as $s)
                <li class="border rounded p-3 bg-white">{{ $s->carrier }} - {{ $s->tracking_number }} ({{ $s->status }})</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $shippings->links() }}</div>
    @endif
@endsection