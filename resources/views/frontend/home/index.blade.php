@extends('layouts.frontend')
@section('title', __('NEXTON - Curated Tech & Lifestyle Marketplace'))

@section('content')
@php
    $categoryIcons = [
        '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17V9m4 8V5m4 12v-6M4 17h16"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v12H4zM8 21h8M12 17v4"/>',
        '<rect x="7" y="2" width="10" height="20" rx="2"/><path stroke-linecap="round" d="M11 18h2"/>',
        '<circle cx="12" cy="12" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 3-3 5 5M3 21h18"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M6 12V7h12v5M6 12v7h12v-7M9 19v2h6v-2"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h4l2-2h4l2 2h4v13H4z"/><circle cx="12" cy="13" r="4"/>',
    ];
    $productIcons = [
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v11H4zM8 20h8M12 16v4"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 14v-2a8 8 0 0116 0v2M4 13H3v6h4v-6H4zm16 0h1v6h-4v-6h3z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10a3 3 0 013 3v5a3 3 0 01-3 3H7a3 3 0 01-3-3v-5a3 3 0 013-3zM8 10v5m-2-2.5h4M16 11h.01M18 13h.01"/>',
        '<circle cx="12" cy="12" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>',
        '<rect x="7" y="2" width="10" height="20" rx="2"/><path stroke-linecap="round" d="M11 18h2"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h4l2-2h4l2 2h4v13H4z"/><circle cx="12" cy="13" r="4"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 3-3 5 5M3 21h18"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M6 12V7h12v5M6 12v7h12v-7M9 19v2h6v-2"/>',
    ];
@endphp

<div class="ui-page">
    <h1 class="sr-only">{{ __('Curated tech and lifestyle marketplace') }}</h1>
    
    <section class="mb-6 lg:hidden" aria-labelledby="mobile-offer-title">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 via-brand-600 to-ink-900 p-6 text-white shadow-lg shadow-brand-500/10">
            <div class="relative z-10 max-w-[80%]">
                <span class="mb-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] ring-1 ring-inset ring-white/20">{{ __('Special offer') }}</span>
                <h2 id="mobile-offer-title" class="text-xl font-black leading-tight">{{ __('Discover your next favorite from verified sellers') }}</h2>
                <x-ui.button :href="route('products.index')" variant="dark" class="mt-5">{{ __('Shop now') }}</x-ui.button>
            </div>
            <svg class="pointer-events-none absolute -bottom-10 -right-8 h-40 w-40 text-white/10" viewBox="0 0 200 200" fill="currentColor" aria-hidden="true">
                <circle cx="100" cy="100" r="80"/>
                <circle cx="100" cy="100" r="52" fill="none" stroke="currentColor" stroke-width="2"/>
            </svg>
        </div>
    </section>

    <nav id="categories" class="ui-scrollbar mb-9 overflow-x-auto pb-3" aria-label="{{ __('Shop by category') }}">
        <h2 class="sr-only">{{ __('Shop by category') }}</h2>
        <div class="flex min-w-max items-start gap-5 sm:gap-7">
            <a href="{{ route('products.index') }}" class="group flex w-20 flex-col items-center gap-2 rounded-xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                <span class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-brand-200 bg-white text-brand-600 shadow-sm transition group-hover:border-brand-500 group-hover:bg-brand-50">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
                </span>
                <span class="text-center text-xs font-bold text-slate-600 transition group-hover:text-brand-700">{{ __('All') }}</span>
            </a>
            @foreach ($categories as $idx => $cat)
                <a href="{{ route('products.index', ['category' => $cat->id]) }}" class="group flex w-20 flex-col items-center gap-2 rounded-xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-slate-100 bg-white text-accent-600 shadow-sm transition group-hover:border-accent-300 group-hover:bg-accent-50">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $categoryIcons[$idx % count($categoryIcons)] !!}</svg>
                    </span>
                    <span class="text-center text-xs font-semibold leading-4 text-slate-600 transition group-hover:text-brand-700">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <section class="mb-14" aria-label="{{ __('Featured collections') }}">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="{{ $spotlightProduct ? 'lg:col-span-7' : 'lg:col-span-12' }} group relative flex min-h-[26rem] flex-col justify-between overflow-hidden rounded-3xl bg-ink-900 p-6 text-white shadow-sm sm:min-h-[30rem] sm:p-8">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" style="background-image: url('https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1200&q=80');" aria-hidden="true"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-ink-900/35 to-ink-900/45" aria-hidden="true"></div>

                <div class="relative z-10">
                    <span class="inline-flex max-w-lg items-center gap-2.5 rounded-2xl bg-white/15 px-4 py-2 text-xs font-semibold backdrop-blur-md ring-1 ring-inset ring-white/15">
                        <svg class="h-5 w-5 shrink-0 text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        {{ __('Complete your interior with minimalist designer tech') }}
                    </span>
                </div>

                <div class="relative z-10 flex flex-col gap-5 rounded-2xl border border-white/10 bg-ink-900/45 p-4 backdrop-blur-md sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 text-brand-200 ring-1 ring-inset ring-white/10">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M6 12V7h12v5M6 12v7h12v-7M9 19v2h6v-2"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold">{{ __('Curated Living Space') }}</h2>
                            <p class="mt-0.5 text-xs text-slate-300">{{ __('Nexton Design Studio') }}</p>
                        </div>
                    </div>
                    <x-ui.button :href="route('products.index')" variant="dark" class="self-start bg-white text-ink-900 hover:bg-slate-100 sm:self-auto">{{ __('Explore collection') }}</x-ui.button>
                </div>
            </div>

            @if ($spotlightProduct)
                @php $spotlightStock = (int) ($spotlightProduct->inventory?->quantity ?? 0); @endphp
                <div class="relative flex flex-col justify-between overflow-hidden rounded-3xl border border-accent-100 bg-gradient-to-br from-accent-50 via-white to-brand-50 p-6 shadow-sm sm:p-8 lg:col-span-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <x-ui.badge tone="accent">{{ __('Spotlight') }}</x-ui.badge>
                        <form method="POST" action="{{ route('wishlist.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $spotlightProduct->id }}">
                            <button type="submit" class="ui-icon-btn border border-slate-200 bg-white/90 shadow-sm hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500" aria-label="{{ __('Save to wishlist') }}" title="{{ __('Save to wishlist') }}">
                                <svg class="h-5 w-5 transition {{ in_array($spotlightProduct->id, $wishlistedProductIds, true) ? 'fill-rose-500 text-rose-500' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                        </form>
                    </div>

                    <a href="{{ route('products.show', $spotlightProduct) }}" class="my-auto flex items-center justify-center py-6 rounded-2xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20" aria-label="{{ $spotlightProduct->name }}">
                        <span class="relative flex h-52 w-52 items-center justify-center sm:h-60 sm:w-60">
                            <span class="absolute inset-0 rounded-full bg-white/70 blur-xl" aria-hidden="true"></span>
                            <svg class="relative h-28 w-28 text-accent-500 sm:h-32 sm:w-32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                    </a>

                    <div class="flex flex-col gap-4 border-t border-slate-200/80 pt-5 sm:flex-row sm:items-end sm:justify-between">
                        <div class="min-w-0">
                            <a href="{{ route('products.show', $spotlightProduct) }}" class="rounded-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                                <h2 class="break-words text-lg font-bold text-slate-900 sm:text-xl">{{ $spotlightProduct->name }}</h2>
                            </a>
                            <p class="mt-1 text-xs text-slate-500">{{ $spotlightProduct->vendor->business_name }}</p>
                            <x-ui.badge :tone="$spotlightStock > 0 ? 'success' : 'danger'" class="mt-2">{{ $spotlightStock > 0 ? __('In stock') : __('Out of stock') }}</x-ui.badge>
                        </div>
                        <form method="POST" action="{{ route('cart.store') }}" class="shrink-0">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $spotlightProduct->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <x-ui.button type="submit" variant="dark" :disabled="$spotlightStock < 1" class="whitespace-nowrap">
                                {{ __('Add to cart') }} <span class="text-brand-300">৳ {{ number_format($spotlightProduct->price, 0) }}</span>
                            </x-ui.button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section id="popular" class="mb-14" aria-labelledby="popular-title">
        <div class="mb-7 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
            <h2 id="popular-title" class="text-2xl font-extrabold text-ink-900 sm:text-3xl">{{ __('Popular products') }}</h2>
            <div class="ui-scrollbar flex items-center gap-2 overflow-x-auto pb-2">
                <a href="{{ route('products.index') }}" class="inline-flex min-h-11 shrink-0 items-center whitespace-nowrap rounded-full bg-ink-900 px-4 text-xs font-bold text-white transition hover:bg-ink-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">{{ __('All') }}</a>
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->id]) }}" class="inline-flex min-h-11 shrink-0 items-center whitespace-nowrap rounded-full border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 min-[420px]:grid-cols-2 sm:gap-6 lg:grid-cols-4">
            @forelse ($featuredProducts as $index => $product)
                @php
                    $stock = (int) ($product->inventory?->quantity ?? 0);
                    $available = $stock > 0;
                @endphp
                <x-ui.card :padding="false" class="group relative h-full overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-full flex-col">
                    <form method="POST" action="{{ route('wishlist.store') }}" class="absolute right-4 top-4 z-10">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="ui-icon-btn border border-slate-200 bg-white/95 shadow-sm hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500" aria-label="{{ __('Save to wishlist') }}" title="{{ __('Save to wishlist') }}">
                            <svg class="h-5 w-5 transition {{ in_array($product->id, $wishlistedProductIds, true) ? 'fill-rose-500 text-rose-500' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </button>
                    </form>

                    <a href="{{ route('products.show', $product) }}" class="block rounded-2xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-brand-500/30" aria-label="{{ $product->name }}">
                        <div class="flex h-44 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-50 to-accent-50/60 text-accent-500 transition duration-300 group-hover:from-accent-50 group-hover:to-brand-50 sm:h-52">
                            <svg class="h-16 w-16 transition duration-300 group-hover:scale-105 sm:h-20 sm:w-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">{!! $productIcons[$index % count($productIcons)] !!}</svg>
                        </div>
                    </a>

                    <div class="flex flex-1 flex-col p-4 sm:p-5">
                        <p class="mb-1 text-base font-extrabold text-slate-900 sm:text-lg">৳ {{ number_format($product->price, 2) }}</p>
                        <a href="{{ route('products.show', $product) }}" class="rounded-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                            <h3 class="break-words text-sm font-bold leading-6 text-slate-800 transition group-hover:text-brand-700">{{ $product->name }}</h3>
                        </a>
                        <p class="mt-1 text-xs text-slate-500">{{ $product->brand->name ?? ($product->category->name ?? __('Nexton Tech')) }}</p>

                        <div class="mt-auto flex flex-col gap-3 border-t border-slate-100 pt-4">
                            <x-ui.badge :tone="$available ? 'success' : 'danger'">{{ $available ? __('In stock') : __('Out of stock') }}</x-ui.badge>
                            <form method="POST" action="{{ route('cart.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <x-ui.button type="submit" :disabled="! $available" class="w-full">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('Add to cart') }}
                                </x-ui.button>
                            </form>
                        </div>
                    </div>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card :padding="false" class="col-span-full">
                    <x-ui.empty-state :title="__('No products available yet')" :description="__('Approved products from verified merchants will appear here.')">
                        <x-slot:action>
                            <x-ui.button :href="route('products.index')" variant="outline">{{ __('Browse catalog') }}</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </x-ui.card>
            @endforelse
        </div>

        <div class="mt-10 flex flex-col items-start justify-between gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center">
            <p class="text-sm leading-6 text-slate-500">
                <span class="font-bold text-slate-800">{{ __('Browse the latest approved products') }}</span> {{ __('from verified merchants') }}
            </p>
            <x-ui.button :href="route('products.index')">
                {{ __('See all products') }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
            </x-ui.button>
        </div>
    </section>

    <section id="vendors" class="mb-4" aria-labelledby="vendors-title">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="vendors-title" class="text-2xl font-extrabold text-ink-900">{{ __('Verified merchant stores') }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Official authorized dealers and highly rated vendors in Bangladesh.') }}</p>
            </div>
            <x-ui.button :href="route('vendor.register.create')" variant="outline">
                {{ __('Become a seller') }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
            </x-ui.button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredVendors as $vendor)
                <x-ui.card class="transition hover:border-accent-200 hover:shadow-md">
                    <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-xl font-black text-accent-700 ring-1 ring-inset ring-accent-100" aria-hidden="true">{{ substr($vendor->business_name, 0, 1) }}</span>
                    <div class="min-w-0">
                        <div class="flex items-start gap-1.5">
                            <h3 class="break-words text-sm font-bold text-slate-800">{{ $vendor->business_name }}</h3>
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span class="sr-only">{{ __('Verified vendor') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $vendor->district ?? __('Dhaka, Bangladesh') }}</p>
                        <p class="mt-2 text-[11px] font-semibold text-accent-700">{{ __('Code') }}: {{ $vendor->vendor_code }}</p>
                    </div>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card :padding="false" class="col-span-full">
                    <x-ui.empty-state :title="__('No verified vendors yet')" :description="__('Verified merchant stores will appear here once they join.')" />
                </x-ui.card>
            @endforelse
        </div>
    </section>
</div>
@endsection
