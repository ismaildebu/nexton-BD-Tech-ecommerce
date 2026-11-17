@extends('layouts.frontend')
@section('title', 'My Cart - NEXTON')

@section('content')
<div class="mx-auto max-w-2xl px-4 sm:px-6 py-6 sm:py-10">
    <!-- Top Bar (Image 4 My Cart style) -->
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('products.index') }}" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:text-[#FF5B22] hover:border-[#FF5B22] shadow-xs transition" title="Back">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ __('My Cart') }}</h1>
        <div class="w-10"></div> <!-- Spacer for center alignment -->
    </div>

    @if (! $cart || $cart->items->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-sm">
            <div class="w-20 h-20 rounded-full bg-orange-50 text-[#FF5B22] mx-auto flex items-center justify-center text-3xl mb-4">
                🛒
            </div>
            <h2 class="text-lg font-bold text-slate-800">{{ __('Your cart is currently empty') }}</h2>
            <p class="text-xs text-slate-400 mt-1 mb-6">{{ __('Explore our trending tech, gadgets, and verified merchant products.') }}</p>
            <a href="{{ route('products.index') }}" class="inline-flex px-6 py-3 rounded-full bg-[#181F2C] text-white text-xs font-bold hover:bg-black transition shadow-md">
                {{ __('Start Shopping') }} &rarr;
            </a>
        </div>
    @else
        @php
            $subtotal = 0;
            foreach ($cart->items as $item) {
                if ($item->product) {
                    $subtotal += (float) $item->product->price * $item->quantity;
                }
            }
        @endphp

        <!-- Cart Items List (Image 4 Style) -->
        <div class="space-y-4 mb-8">
            @foreach ($cart->items as $item)
                @php
                    $prod = $item->product;
                    $emojis = ['🎧', '⌚', '💻', '📱', '🎮', '💡'];
                    $emoji = $emojis[$item->id % count($emojis)];
                @endphp
                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <!-- Thumbnail Container -->
                    <div class="w-20 h-20 rounded-2xl bg-[#F4F5F7] flex items-center justify-center text-3xl shrink-0">
                        {{ $emoji }}
                    </div>

                    <!-- Item Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm truncate max-w-[180px] sm:max-w-xs">
                                    {{ $prod->name ?? 'Product' }}
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $prod->category->name ?? 'General Tech' }}
                                </p>
                            </div>

                            <!-- Remove Button / Trash icon (Image 4 style) -->
                            <form method="POST" action="{{ route('cart.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $prod->id ?? 0 }}">
                                <input type="hidden" name="quantity" value="0">
                                <button type="submit" class="text-slate-300 hover:text-rose-500 transition p-1" title="Remove Item">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <!-- Price and Quantity Controls -->
                        <div class="flex items-center justify-between mt-3">
                            <span class="font-extrabold text-slate-900 text-sm">
                                ৳ {{ number_format($prod->price ?? 0, 2) }}
                            </span>

                            <!-- Quantity Counter (Image 4 style: - qty +) -->
                            <div class="flex items-center bg-slate-100 rounded-full px-2.5 py-1 gap-3">
                                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $prod->id ?? 0 }}">
                                    <input type="hidden" name="quantity" value="{{ max(0, $item->quantity - 1) }}">
                                    <button type="submit" class="text-slate-600 font-bold hover:text-black text-xs px-1">-</button>
                                </form>
                                <span class="font-bold text-xs text-slate-800">{{ $item->quantity }}</span>
                                <form method="POST" action="{{ route('cart.store') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $prod->id ?? 0 }}">
                                    <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                    <button type="submit" class="text-slate-600 font-bold hover:text-black text-xs px-1">+</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Discount Code Card (Image 4 Style) -->
        <div class="bg-white rounded-3xl p-3 border border-slate-200/80 shadow-xs mb-6 flex items-center justify-between gap-3" x-data="{ code: '', applied: false }">
            <div class="flex-1 pl-3">
                <input type="text" x-model="code" placeholder="{{ __('Enter Discount Code') }}" class="w-full text-xs bg-transparent focus:outline-none text-slate-800 placeholder-slate-400 font-medium uppercase">
            </div>
            <button type="button" @click="if(code.trim().toUpperCase() === 'SAVE10') { applied = true; alert('Coupon SAVE10 applied! 10% discount.'); } else { alert('Enter a valid code like SAVE10'); }" class="px-5 py-2.5 rounded-full bg-orange-50 text-[#FF5B22] text-xs font-extrabold hover:bg-orange-100 transition">
                {{ __('Apply') }}
            </button>
        </div>

        <!-- Order Summary Card (Image 4 Style) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs mb-8 space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>{{ __('Subtotal') }}</span>
                <span class="font-bold text-slate-800">৳ {{ number_format($subtotal, 2) }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>{{ __('Delivery & Shipping') }}</span>
                <span class="text-emerald-600 font-bold">{{ __('Free') }}</span>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-900">{{ __('Total') }}</span>
                <span class="text-xl font-black text-slate-900">৳ {{ number_format($subtotal, 2) }}</span>
            </div>
        </div>

        <!-- Checkout Action Button (Image 4 Style: Full-width bright orange pill button) -->
        <form method="POST" action="{{ route('checkout.store') }}">
            @csrf
            <button type="submit" class="w-full py-4 rounded-full bg-[#FF5B22] hover:bg-[#e04e1b] text-white text-base font-extrabold shadow-lg hover:shadow-xl transition transform active:scale-[0.99] flex items-center justify-center gap-2">
                <span>{{ __('Checkout') }}</span>
                <span class="opacity-90">&bull;</span>
                <span>৳ {{ number_format($subtotal, 2) }}</span>
            </button>
        </form>
    @endif
</div>
@endsection
