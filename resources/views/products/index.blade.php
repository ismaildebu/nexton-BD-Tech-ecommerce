@extends('layouts.frontend')
@section('title', __('Product Catalog - :store', ['store' => $siteSettings->site_name]))

@section('content')
@php
    $productIcons = [
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v11H4zM8 20h8M12 16v4"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 14v-2a8 8 0 0116 0v2M4 13H3v6h4v-6H4zm16 0h1v6h-4v-6h3z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10a3 3 0 013 3v5a3 3 0 01-3 3H7a3 3 0 01-3-3v-5a3 3 0 013-3zM8 10v5m-2-2.5h4M16 11h.01M18 13h.01"/>',
        '<rect x="7" y="2" width="10" height="20" rx="2"/><path stroke-linecap="round" d="M11 18h2"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h4l2-2h4l2 2h4v13H4z"/><circle cx="12" cy="13" r="4"/>',
        '<circle cx="12" cy="12" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 3-3 5 5M3 21h18"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M6 12V7h12v5M6 12v7h12v-7M9 19v2h6v-2"/>',
    ];
@endphp

<div class="ui-page">
    <x-ui.page-header
        :title="__('Product catalog')"
        :description="__('Explore authentic electronics, living essentials, and tech accessories from verified merchants.')"
    />

    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('products.index') }}" role="search" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
            @if (request()->filled('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <div class="relative">
                <label for="catalog-search" class="ui-label">{{ __('Search products') }}</label>
                <input id="catalog-search" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search products...') }}" class="pl-11">
                <svg class="pointer-events-none absolute left-4 top-[2.65rem] h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <x-ui.button type="submit" class="w-full sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16l-6 7v5l-4 2v-7L4 5z"/></svg>
                {{ __('Apply filters') }}
            </x-ui.button>
        </form>
    </x-ui.card>

    <nav class="ui-scrollbar mb-7 flex items-center gap-2 overflow-x-auto pb-2" aria-label="{{ __('Product categories') }}">
        <a href="{{ route('products.index', array_filter(['q' => request('q')])) }}" @class(['inline-flex min-h-11 shrink-0 items-center whitespace-nowrap rounded-full px-4 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20', 'bg-ink-900 text-white hover:bg-ink-800' => ! request()->filled('category'), 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700' => request()->filled('category')]) @if (! request()->filled('category')) aria-current="true" @endif>
            {{ __('All') }}
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('products.index', array_filter(['category' => $category->id, 'q' => request('q')])) }}" @class(['inline-flex min-h-11 shrink-0 items-center whitespace-nowrap rounded-full px-4 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20', 'bg-ink-900 text-white hover:bg-ink-800' => (string) request('category') === (string) $category->id, 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700' => (string) request('category') !== (string) $category->id]) @if ((string) request('category') === (string) $category->id) aria-current="true" @endif>
                {{ $category->name }}
            </a>
        @endforeach
    </nav>

    @if ($products->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state :title="__('No products found')" :description="__('Try a different keyword or browse the full catalog.')">
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </x-slot:icon>
                <x-slot:action>
                    <x-ui.button :href="route('products.index')">{{ __('Reset search') }}</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="mb-3 flex items-center justify-between gap-3 text-sm text-slate-500">
            <p>{{ __('Showing :from–:to of :total products', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()]) }}</p>
            <span class="hidden text-xs sm:inline">{{ __('Prices in BDT') }}</span>
        </div>

        <div class="grid grid-cols-1 gap-4 min-[420px]:grid-cols-2 sm:gap-6 lg:grid-cols-4">
            @foreach ($products as $index => $product)
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
                            <h2 class="break-words text-sm font-bold leading-6 text-slate-800 transition group-hover:text-brand-700">{{ $product->name }}</h2>
                        </a>
                        <p class="mt-1 text-xs text-slate-500">{{ $product->category->name ?? __(':store Marketplace', ['store' => $siteSettings->site_name]) }}</p>

                        <div class="mt-auto flex flex-col gap-3 border-t border-slate-100 pt-4">
                            <x-ui.badge :tone="$available ? 'success' : 'danger'">{{ $available ? __('In stock') : __('Out of Stock') }}</x-ui.badge>
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
            @endforeach
        </div>

        <div class="ui-pagination mt-8 flex justify-center">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
