@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold text-[#253442] mb-6">{{ __('Customer Dashboard') }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-[#E8EBF0] p-5">
            <p class="text-sm text-gray-500">{{ __('Orders') }}</p>
            <p class="text-2xl font-bold text-[#6852D9]">{{ $ordersCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-[#E8EBF0] p-5">
            <p class="text-sm text-gray-500">{{ __('Wishlist') }}</p>
            <p class="text-2xl font-bold text-[#6852D9]">{{ $wishlistCount }}</p>
        </div>
    </div>
    <h2 class="text-lg font-semibold text-[#253442] mb-3">{{ __('Recent Orders') }}</h2>
    @if ($recentOrders->isEmpty())
        <p class="text-gray-500">{{ __('No orders yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($recentOrders as $order)
                <li class="bg-white border border-[#E8EBF0] rounded-lg p-3 flex justify-between">
                    <span>{{ $order->order_number }}</span>
                    <span class="text-[#6852D9] font-semibold">{{ number_format($order->total, 2) }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
