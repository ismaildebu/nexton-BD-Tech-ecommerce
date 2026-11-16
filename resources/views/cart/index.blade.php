@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Cart') }}</h1>
    @if ($cart->items->isEmpty())
        <p class="text-gray-500">{{ __('Your cart is empty.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($cart->items as $item)
                <li class="border rounded p-3 bg-white">{{ $item->product->name ?? '-' }} x {{ $item->quantity }}</li>
            @endforeach
        </ul>
        <form method="POST" action="{{ route('checkout.store') }}" class="mt-4">
            @csrf
            <button type="submit" class="bg-[#6852D9] text-white px-4 py-2 rounded">{{ __('Checkout') }}</button>
        </form>
    @endif
@endsection