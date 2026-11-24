@extends('layouts.frontend')
@section('title', __('Wishlist - :store', ['store' => $siteSettings->site_name]))

@section('content')
<div class="ui-page-narrow">
    <x-ui.page-header :title="__('Wishlist')" :description="__('Products you saved for later.')">
        <x-slot:action>
            <x-ui.button :href="route('products.index')" variant="outline">
                {{ __('Continue shopping') }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    @if ($items->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state :title="__('Your wishlist is empty')" :description="__('Save products you like and find them here when you are ready to buy.')">
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </x-slot:icon>
                <x-slot:action>
                    <x-ui.button :href="route('products.index')">{{ __('Explore products') }}</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="space-y-4">
            @foreach ($items as $wishlistItem)
                @php
                    $product = $wishlistItem->product;
                    $stock = (int) ($product?->inventory?->quantity ?? 0);
                    $vendor = $product?->vendor;
                    $available = $product
                        && $product->status === 'approved'
                        && $product->is_active
                        && $vendor?->verification_status?->value === 'approved'
                        && $vendor?->status?->value === 'active';
                @endphp
                <x-ui.card :padding="false" class="p-4 sm:p-5">
                    <article class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-50 to-accent-50 text-accent-500" aria-hidden="true">
                                <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div class="min-w-0">
                                @if ($product)
                                    <h2>
                                        @if ($available)
                                            <a href="{{ route('products.show', $product) }}" class="rounded-md text-sm font-bold leading-6 text-slate-800 transition hover:text-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 sm:text-base">{{ $product->name }}</a>
                                        @else
                                            <span class="text-sm font-bold leading-6 text-slate-800 sm:text-base">{{ $product->name }}</span>
                                        @endif
                                    </h2>
                                    <p class="mt-1 text-sm font-extrabold text-slate-900">৳ {{ number_format($product->price, 2) }}</p>
                                    <x-ui.badge :tone="$available && $stock > 0 ? 'success' : 'danger'" class="mt-2">{{ $available && $stock > 0 ? __('In stock') : __('Currently unavailable') }}</x-ui.badge>
                                @else
                                    <h2 class="text-sm font-semibold text-slate-500">{{ __('Product no longer available') }}</h2>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                            @if ($available && $stock > 0)
                                <form method="POST" action="{{ route('cart.store') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <x-ui.button type="submit" class="w-full sm:w-auto">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 11.6a2 2 0 002 1.6h7.7a2 2 0 002-1.6L21 7H6M9 20a1 1 0 11-2 0 1 1 0 012 0zm10 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                                        {{ __('Add to cart') }}
                                    </x-ui.button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('wishlist.destroy', $wishlistItem) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="outline" class="w-full text-rose-600 hover:border-rose-200 hover:bg-rose-50 sm:w-auto">{{ __('Remove') }}</x-ui.button>
                            </form>
                        </div>
                    </article>
                </x-ui.card>
            @endforeach
        </div>
        <div class="ui-pagination mt-6">{{ $items->links() }}</div>
    @endif
</div>
@endsection
