@extends('layouts.frontend')
@section('title', $product->name . ' - NEXTON')

@section('content')
<div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-4 sm:py-8" 
     x-data="{ 
        quantity: 1, 
        selectedColor: 'black', 
        activeTab: 'desc', 
        inWishlist: false,
        copied: false 
     }">

    <!-- Top Action Bar (Back, Title, Share, Wishlist) -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('products.index') }}" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:text-[#FF5B22] hover:border-[#FF5B22] shadow-xs transition" title="Back to Products">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        <div class="flex items-center gap-2">
            <!-- Share button -->
            <button @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2000)" 
                    class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:text-[#FF5B22] shadow-xs transition" title="Share Product">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
            </button>
            <span x-show="copied" class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full absolute -top-8 right-12" x-cloak>Link Copied!</span>

            <!-- Wishlist Button -->
            <button @click="inWishlist = !inWishlist" 
                    class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:text-rose-500 shadow-xs transition" title="Save to Wishlist">
                <svg class="w-5 h-5 transition" :class="inWishlist ? 'fill-rose-500 text-rose-500' : 'fill-none'" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Product Showcase Gallery (Image 4 Style) -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm mb-6">
        <div class="relative flex items-center justify-center h-64 sm:h-80 bg-[#F4F5F7] rounded-2xl overflow-hidden mb-4">
            <div class="text-8xl sm:text-9xl transform hover:scale-110 transition duration-300 select-none">
                🎧
            </div>
        </div>

        <!-- Dot Carousel Indicators -->
        <div class="flex items-center justify-center gap-2">
            <span class="w-6 h-2 rounded-full bg-[#181F2C]"></span>
            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
        </div>
    </div>

    <!-- Product Info Card (Image 4 Style) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm mb-8">
        <!-- Title & Price -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $product->name }}</h1>
                <div class="flex items-center gap-3 mt-2 text-xs">
                    <span class="inline-flex items-center gap-1 font-bold text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md">
                        ★ 4.8
                    </span>
                    <span class="text-slate-400 font-medium">(320 Reviews)</span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-slate-600 font-semibold">
                        Seller: <span class="text-[#FF5B22] font-bold">{{ $product->vendor->business_name ?? 'Nexton Official' }}</span>
                    </span>
                </div>
            </div>
            <div class="sm:text-right">
                <div class="text-3xl font-black text-slate-900">৳ {{ number_format($product->price, 2) }}</div>
                <div class="text-xs font-semibold text-emerald-600 mt-0.5">In Stock &bull; Ready to ship</div>
            </div>
        </div>

        <!-- Color Selector (Image 4 Style) -->
        <div class="py-6 border-b border-slate-100">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Color Option</h3>
            <div class="flex items-center gap-3">
                <!-- Red -->
                <button @click="selectedColor = 'red'" 
                        :class="selectedColor === 'red' ? 'ring-2 ring-offset-2 ring-[#FF5B22] scale-110' : 'opacity-80 hover:opacity-100'"
                        class="w-7 h-7 rounded-full bg-[#8B0000] transition transform"></button>
                <!-- Black -->
                <button @click="selectedColor = 'black'" 
                        :class="selectedColor === 'black' ? 'ring-2 ring-offset-2 ring-[#FF5B22] scale-110' : 'opacity-80 hover:opacity-100'"
                        class="w-7 h-7 rounded-full bg-[#181F2C] transition transform"></button>
                <!-- Blue -->
                <button @click="selectedColor = 'blue'" 
                        :class="selectedColor === 'blue' ? 'ring-2 ring-offset-2 ring-[#FF5B22] scale-110' : 'opacity-80 hover:opacity-100'"
                        class="w-7 h-7 rounded-full bg-[#1E3A8A] transition transform"></button>
                <!-- Brown -->
                <button @click="selectedColor = 'brown'" 
                        :class="selectedColor === 'brown' ? 'ring-2 ring-offset-2 ring-[#FF5B22] scale-110' : 'opacity-80 hover:opacity-100'"
                        class="w-7 h-7 rounded-full bg-[#78350F] transition transform"></button>
                <!-- Grey -->
                <button @click="selectedColor = 'grey'" 
                        :class="selectedColor === 'grey' ? 'ring-2 ring-offset-2 ring-[#FF5B22] scale-110' : 'opacity-80 hover:opacity-100'"
                        class="w-7 h-7 rounded-full bg-[#D1D5DB] transition transform"></button>
            </div>
        </div>

        <!-- Tabbed Details (Description, Specifications, Reviews - Image 4 Style) -->
        <div class="pt-6">
            <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                <button @click="activeTab = 'desc'" 
                        :class="activeTab === 'desc' ? 'bg-[#FF5B22] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition">
                    Description
                </button>
                <button @click="activeTab = 'specs'" 
                        :class="activeTab === 'specs' ? 'bg-[#FF5B22] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition">
                    Specifications
                </button>
                <button @click="activeTab = 'reviews'" 
                        :class="activeTab === 'reviews' ? 'bg-[#FF5B22] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition">
                    Reviews (320)
                </button>
            </div>

            <!-- Description Tab -->
            <div x-show="activeTab === 'desc'" class="text-xs text-slate-600 leading-relaxed space-y-3">
                <p>{{ $product->description ?: 'Designed with precision engineering, premium materials, and cutting-edge audio acoustic drivers. Delivers crisp trebles, balanced midtones, and punchy deep bass. Equipped with active noise cancellation and ergonomic ear-tips for all-day comfort.' }}</p>
                <ul class="list-disc pl-5 space-y-1 font-medium text-slate-700">
                    <li>Bluetooth 5.3 ultra-low latency connection</li>
                    <li>Up to 32 hours playback with fast wireless charging case</li>
                    <li>IPX5 sweat and splash resistance for sports & workouts</li>
                </ul>
            </div>

            <!-- Specifications Tab -->
            <div x-show="activeTab === 'specs'" class="text-xs text-slate-600" x-cloak>
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 rounded-xl"><span class="font-bold text-slate-800">Brand:</span> {{ $product->brand->name ?? 'Nexton Tech' }}</div>
                    <div class="p-3 bg-slate-50 rounded-xl"><span class="font-bold text-slate-800">Category:</span> {{ $product->category->name ?? 'Electronics' }}</div>
                    <div class="p-3 bg-slate-50 rounded-xl"><span class="font-bold text-slate-800">Warranty:</span> 1 Year Official Replacement</div>
                    <div class="p-3 bg-slate-50 rounded-xl"><span class="font-bold text-slate-800">Origin:</span> Authorized Import</div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div x-show="activeTab === 'reviews'" class="text-xs text-slate-600 space-y-3" x-cloak>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-slate-800">Sabbir Ahmed</span>
                        <span class="text-amber-500 font-bold">★★★★★</span>
                    </div>
                    <p class="text-slate-500">Amazing sound quality and battery life. Delivered within 24 hours in Dhaka!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky Bottom Bar: Quantity Selector & Add to Cart (Image 4 Style) -->
    <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-md border border-slate-200/80 rounded-2xl p-3 sm:p-4 shadow-xl flex items-center justify-between gap-4">
        <!-- Quantity Selector (- 1 +) -->
        <div class="flex items-center bg-slate-100 rounded-full px-3 py-1.5 gap-4">
            <button type="button" @click="if (quantity > 1) quantity--" class="w-7 h-7 rounded-full bg-white text-slate-700 font-extrabold flex items-center justify-center hover:bg-slate-200 transition text-sm">
                -
            </button>
            <span class="font-extrabold text-sm text-slate-800 w-4 text-center" x-text="quantity"></span>
            <button type="button" @click="quantity++" class="w-7 h-7 rounded-full bg-white text-slate-700 font-extrabold flex items-center justify-center hover:bg-slate-200 transition text-sm">
                +
            </button>
        </div>

        <!-- Add to Cart Form -->
        <form method="POST" action="{{ route('cart.store') }}" class="flex-1 max-w-md">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" :value="quantity">
            <button type="submit" class="w-full py-3.5 px-6 rounded-full bg-[#FF5B22] hover:bg-[#e04e1b] text-white text-sm font-extrabold shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Add to Cart</span>
                <span class="opacity-90">&bull;</span>
                <span x-text="'৳ ' + ({{ $product->price }} * quantity).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
            </button>
        </form>
    </div>
</div>
@endsection
