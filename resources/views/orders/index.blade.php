@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Orders') }}</h1>
    @if ($orders->isEmpty())
        <p class="text-gray-500">{{ __('No orders yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($orders as $order)
                <li class="border rounded p-3 bg-white">{{ $order->order_number }} - ৳ {{ number_format($order->total, 2) }} ({{ $order->status }})</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection