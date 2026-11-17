@extends('layouts.frontend')
@section('title', 'Catalog & Products - NEXTON')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header with Search & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#181F2C] tracking-tight">{{ __('Product Catalog') }}</h1>
            <p class="text-xs text-slate-400 mt-1">{{ __('Explore our authentic electronics, living essentials, and tech accessories.') }}</p>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2 max-w-md w-full">
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search products...') }}" 
                       class="w-full pl-10 pr-4 py-2.5 text-xs bg-white border border-slate-200 rounded-full focus:outline-none focus:ring-2 focus:ring-[#FF5B22]/30 focus:border-[#FF5B22] shadow-2xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-full bg-[#181F2C] text-white text-xs font-bold hover:bg-black transition shadow-xs">
                {{ __('Filter') }}
            </button>
        </form>
    </div>

    @if ($products->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-sm">
            <div class="text-4xl mb-3">🔍</div>
            <h2 class="text-lg font-bold text-slate-800">{{ __('No products found') }}</h2>
            <p class="text-xs text-slate-400 mt-1 mb-6">{{ __('Try searching for a different keyword or view all items.') }}</p>
            <a href="{{ route('products.index') }}" class="inline-flex px-6 py-2.5 rounded-full bg-[#FF5B22] text-white text-xs font-bold">
                {{ __('Reset Search') }}
            </a>
        </div>
    @else
        <!-- 4-Column Product Grid (Image 2 Style) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-10">
            @foreach ($products as $index => $product)
                @php
                    $mockIcons = ['💻', '🎧', '🎮', '⌚', '📱', '📷', '👟', '🛋️'];
                    $prodIcon = $mockIcons[$index % count($mockIcons)];
                @endphp
                <div class="group bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between relative" x-data="{ inWishlist: false }">
                    <!-- Wishlist Heart Button -->
                    <button @click="inWishlist = !inWishlist" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/90 backdrop-blur-xs border border-slate-100 flex items-center justify-center text-slate-400 hover:text-rose-500 shadow-xs transition">
                        <svg class="w-4 h-4 transition" :class="inWishlist ? 'fill-rose-500 text-rose-500' : 'fill-none'" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>

                    <!-- Image Link -->
                    <a href="{{ route('products.show', $product) }}" class="block">
                        <div class="w-full h-44 sm:h-52 rounded-2xl bg-[#F4F5F7] flex items-center justify-center overflow-hidden mb-4 group-hover:scale-[1.02] transition duration-300">
                            <span class="text-5xl sm:text-6xl">{{ $prodIcon }}</span>
                        </div>
                    </a>

                    <!-- Details -->
                    <div>
                        <div class="flex items-baseline gap-2 mb-1">
                            <span class="font-extrabold text-slate-900 text-base sm:text-lg">৳ {{ number_format($product->price, 2) }}</span>
                            @if ($product->price > 500)
                                <span class="text-xs text-slate-400 line-through">৳ {{ number_format($product->price * 1.2, 0) }}</span>
                            @endif
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="block">
                            <h3 class="font-bold text-slate-800 text-sm hover:text-[#FF5B22] transition truncate leading-snug">
                                {{ $product->name }}
                            </h3>
                        </a>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">
                            {{ $product->category->name ?? 'Nexton Marketplace' }}
                        </p>
                    </div>

                    <!-- Quick Add Button -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> In Stock
                        </span>
                        <form method="POST" action="{{ route('cart.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-[#FF5B22] hover:text-white transition shadow-2xs" title="Add to Cart">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-center">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
