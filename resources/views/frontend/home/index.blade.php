@extends('layouts.frontend')
@section('title', 'NEXTON - Curated Tech & Lifestyle Marketplace')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4 sm:pt-8" x-data="{ activeCategory: 'all', heroSlide: 1 }">
    
    <!-- Mobile Promo Banner (Image 4 Mobile View) -->
    <div class="md:hidden mb-6">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500 via-orange-500 to-[#FF5B22] p-6 text-white shadow-lg">
            <div class="relative z-10 max-w-[65%]">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase tracking-wider mb-2">Special Offer</span>
                <h2 class="text-xl font-black leading-tight">Super Sale Discount<br><span class="text-amber-100">Up to 50%</span></h2>
                <a href="{{ route('products.index') }}" class="inline-block mt-4 px-5 py-2 rounded-full bg-[#181F2C] text-white text-xs font-bold shadow-md hover:bg-black transition">
                    Shop Now
                </a>
            </div>
            <!-- Decorative image/badge -->
            <div class="absolute -right-4 -bottom-4 w-32 h-32 opacity-90 pointer-events-none">
                <svg viewBox="0 0 200 200" class="w-full h-full fill-white/15">
                    <circle cx="100" cy="100" r="80" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Category Story Circles (Image 4 Mobile & Tablet) -->
    <div class="mb-8 overflow-x-auto pb-2 scrollbar-none" id="categories">
        <div class="flex items-center gap-4 sm:gap-6 min-w-max">
            <a href="{{ route('products.index') }}" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-slate-100 to-slate-200 border-2 border-orange-500/20 group-hover:border-[#FF5B22] p-0.5 flex items-center justify-center transition shadow-xs">
                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center text-xl">
                        ⚡
                    </div>
                </div>
                <span class="text-[11px] font-bold text-slate-700 group-hover:text-[#FF5B22]">All</span>
            </a>
            @foreach ($categories as $idx => $cat)
                @php
                    $icons = ['💻', '🎧', '📱', '⌚', '👟', '✨', '🛋️', '📷'];
                    $icon = $icons[$idx % count($icons)];
                @endphp
                <a href="{{ route('products.index', ['category' => $cat->id]) }}" class="flex flex-col items-center gap-2 group">
                    <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-slate-100 to-slate-200 border-2 border-transparent group-hover:border-[#FF5B22] p-0.5 flex items-center justify-center transition shadow-xs">
                        <div class="w-full h-full rounded-full bg-white flex items-center justify-center text-xl">
                            {{ $icon }}
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-600 group-hover:text-[#FF5B22] max-w-[68px] truncate text-center">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Desktop Hero Section (Image 2 ESTORE Style: 2 Cards Side-by-Side) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-16">
        <!-- Left Hero Card: Minimalist Lifestyle Banner -->
        <div class="lg:col-span-7 relative h-[440px] sm:h-[480px] rounded-3xl overflow-hidden bg-gradient-to-tr from-slate-800 to-slate-900 shadow-sm flex flex-col justify-between p-6 sm:p-8 text-white group">
            <!-- Background Image overlay -->
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
                 style="background-image: url('https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1200&q=80');">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/40"></div>

            <!-- Top Tag Pill (Image 2 style) -->
            <div class="relative z-10 flex items-start justify-between">
                <div class="flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white/20 backdrop-blur-md text-white text-xs font-semibold max-w-sm">
                    <div class="w-5 h-5 rounded-full bg-white/30 flex items-center justify-center text-[10px]">⚡</div>
                    <span>Complete your interior with minimalist designer tech</span>
                </div>
            </div>

            <!-- Bottom Frosted Pill with Slider (Image 2 style) -->
            <div class="relative z-10 flex items-center justify-between p-3 sm:p-4 rounded-2xl bg-black/40 backdrop-blur-md border border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-2xl overflow-hidden shrink-0">
                        🛋️
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-white">Curated Living Space</h4>
                        <p class="text-xs text-white/70">Nexton Design Studio</p>
                    </div>
                </div>

                <!-- Slider arrows & count < 1/5 > -->
                <div class="flex items-center gap-3 text-xs font-medium text-white/80">
                    <button class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                        &larr;
                    </button>
                    <span>1/5</span>
                    <button class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                        &rarr;
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Hero Card: Product Spotlight (Image 2 style with Cast Lights & Add to Cart) -->
        <div class="lg:col-span-5 rounded-3xl bg-[#ECEEEF] p-6 sm:p-8 flex flex-col justify-between shadow-sm relative overflow-hidden">
            <!-- Top Color Options & Wishlist -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full bg-slate-700 ring-2 ring-white"></span>
                    <span class="w-4 h-4 rounded-full bg-slate-900"></span>
                    <span class="w-4 h-4 rounded-full bg-amber-700"></span>
                </div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-white/80 px-2.5 py-1 rounded-full">Spotlight</span>
            </div>

            <!-- Center Product Showcase -->
            <div class="my-auto py-6 flex items-center justify-center">
                <div class="relative flex items-center justify-center w-52 h-52 sm:w-60 sm:h-60">
                    <div class="absolute inset-0 bg-white/60 rounded-full blur-xl"></div>
                    <!-- Product Graphic / Visual -->
                    <div class="relative z-10 text-center">
                        <div class="text-7xl sm:text-8xl transform hover:scale-110 transition duration-300">
                            💡
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Title & Add to Cart Pill (Image 2 style) -->
            <div class="pt-4 border-t border-slate-300/40 flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg sm:text-xl">
                        {{ $spotlightProduct->name ?? 'Cast Lights Minimal' }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ $spotlightProduct->vendor->business_name ?? 'GlowGalore Store' }}
                    </p>
                </div>
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $spotlightProduct->id ?? 1 }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="px-5 py-3 rounded-full bg-[#181F2C] text-white text-xs font-bold hover:bg-black transition shadow-md whitespace-nowrap flex items-center gap-1.5">
                        <span>Add to cart</span>
                        <span class="text-orange-400 font-extrabold">৳ {{ number_format($spotlightProduct->price ?? 4590, 0) }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Section: Popular Products (Image 2 Style) -->
    <div class="mb-16" id="popular">
        <!-- Section Header with Filter Pills -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#181F2C] tracking-tight">
                {{ __('Popular products') }}
            </h2>

            <!-- Category Pills Filter (Image 2 style) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-[#181F2C] text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap shadow-2xs">
                    All
                </button>
                <button @click="activeCategory = 'electronics'" :class="activeCategory === 'electronics' ? 'bg-[#181F2C] text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap shadow-2xs">
                    Electronics
                </button>
                <button @click="activeCategory = 'gadgets'" :class="activeCategory === 'gadgets' ? 'bg-[#181F2C] text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap shadow-2xs">
                    Gadgets & Audio
                </button>
                <button @click="activeCategory = 'fashion'" :class="activeCategory === 'fashion' ? 'bg-[#181F2C] text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap shadow-2xs">
                    Fashion & Wear
                </button>
                <button @click="activeCategory = 'furniture'" :class="activeCategory === 'furniture' ? 'bg-[#181F2C] text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap shadow-2xs">
                    Home & Living
                </button>
            </div>
        </div>

        <!-- 4-Column Product Grid (Image 2 Style Minimalist Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            @forelse ($featuredProducts as $index => $product)
                @php
                    $mockIcons = ['💻', '🎧', '🎮', '⌚', '📱', '📷', '👟', '🛋️'];
                    $prodIcon = $mockIcons[$index % count($mockIcons)];
                @endphp
                <div class="group bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between relative" x-data="{ inWishlist: false }">
                    <!-- Wishlist Heart Button (Top Right) -->
                    <button @click="inWishlist = !inWishlist" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/90 backdrop-blur-xs border border-slate-100 flex items-center justify-center text-slate-400 hover:text-rose-500 shadow-xs transition">
                        <svg class="w-4 h-4 transition" :class="inWishlist ? 'fill-rose-500 text-rose-500' : 'fill-none'" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>

                    <!-- Product Image Container -->
                    <a href="{{ route('products.show', $product) }}" class="block">
                        <div class="w-full h-44 sm:h-52 rounded-2xl bg-[#F4F5F7] flex items-center justify-center overflow-hidden mb-4 group-hover:scale-[1.02] transition duration-300">
                            <span class="text-5xl sm:text-6xl">{{ $prodIcon }}</span>
                        </div>
                    </a>

                    <!-- Product Details (Image 2 style) -->
                    <div>
                        <!-- Price with optional discount -->
                        <div class="flex items-baseline gap-2 mb-1">
                            <span class="font-extrabold text-slate-900 text-base sm:text-lg">৳ {{ number_format($product->price, 2) }}</span>
                            @if ($product->price > 500)
                                <span class="text-xs text-slate-400 line-through">৳ {{ number_format($product->price * 1.2, 0) }}</span>
                            @endif
                        </div>

                        <!-- Product Title -->
                        <a href="{{ route('products.show', $product) }}" class="block">
                            <h3 class="font-bold text-slate-800 text-sm hover:text-[#FF5B22] transition truncate leading-snug">
                                {{ $product->name }}
                            </h3>
                        </a>

                        <!-- Brand / Category Subtitle -->
                        <p class="text-xs text-slate-400 mt-0.5 truncate">
                            {{ $product->brand->name ?? ($product->category->name ?? 'Nexton Tech') }}
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
            @empty
                <div class="col-span-4 py-16 text-center text-slate-400 text-sm">
                    No approved products available right now.
                </div>
            @endforelse
        </div>

        <!-- Bottom Call to Action (Image 2 style) -->
        <div class="mt-10 pt-6 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-slate-500 font-medium">
                <span class="font-bold text-slate-800">1,600+ trending products</span> in 19 verified categories
            </p>
            <a href="{{ route('products.index') }}" class="px-7 py-3.5 rounded-full bg-[#FF5B22] text-white text-xs font-extrabold hover:bg-[#e04e1b] transition shadow-md flex items-center gap-2">
                <span>See all</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Verified Vendors Section -->
    <div class="mb-16" id="vendors">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-[#181F2C] tracking-tight">Verified Merchant Stores</h2>
                <p class="text-xs text-slate-400 mt-1">Official authorized dealers and high-rated vendors in Bangladesh</p>
            </div>
            <a href="{{ route('vendor.register.create') }}" class="text-xs font-bold text-[#FF5B22] hover:underline">
                Become a Seller &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
            @forelse ($featuredVendors as $vendor)
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-50 to-purple-50 border border-purple-100 flex items-center justify-center text-[#6852D9] font-black text-xl shrink-0">
                        {{ substr($vendor->business_name, 0, 1) }}
                    </div>
                    <div class="truncate">
                        <div class="flex items-center gap-1.5">
                            <h3 class="font-bold text-slate-800 text-sm truncate">{{ $vendor->business_name }}</h3>
                            <svg class="w-4 h-4 text-sky-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $vendor->district ?? 'Dhaka, Bangladesh' }}</p>
                        <div class="mt-2 text-[11px] font-semibold text-[#6852D9]">
                            Code: {{ $vendor->vendor_code }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-8 text-center text-slate-400 text-xs">
                    No verified vendors available yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
