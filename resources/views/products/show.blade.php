@extends('layouts.frontend')
@section('title', __(':product - :store', ['product' => $product->name, 'store' => config('app.name')]))

@section('content')
<div class="ui-page max-w-6xl" x-data="{ quantity: 1, maxQuantity: {{ max(1, min(999, $stock)) }}, copied: false, async copyLink() { try { await navigator.clipboard.writeText(window.location.href); this.copied = true; setTimeout(() => this.copied = false, 2000); } catch { this.copied = false; } } }">
    <div class="mb-6 flex items-center justify-between gap-3">
        <x-ui.button :href="route('products.index')" variant="outline">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            {{ __('Back to products') }}
        </x-ui.button>

        <div class="relative flex items-center gap-2">
            <span x-show="copied" x-cloak role="status" aria-live="polite" class="absolute -top-10 right-0 whitespace-nowrap rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ __('Link copied') }}</span>
            <button type="button" @click="copyLink" class="ui-icon-btn border border-slate-200 bg-white shadow-sm hover:border-brand-200 hover:text-brand-600" aria-label="{{ __('Share product') }}" title="{{ __('Share product') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            </button>
            <form method="POST" action="{{ route('wishlist.store') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="ui-icon-btn border border-slate-200 bg-white shadow-sm hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500" aria-label="{{ __('Save to wishlist') }}" title="{{ __('Save to wishlist') }}">
                    <svg class="h-5 w-5 transition {{ $isWishlisted ? 'fill-rose-500 text-rose-500' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </button>
            </form>
        </div>
    </div>

    <x-ui.card :padding="false" class="mb-6 overflow-hidden">
        <div class="flex h-64 items-center justify-center bg-gradient-to-br from-slate-50 via-white to-accent-50/70 text-center text-slate-400 sm:h-80">
            <div>
                <svg class="mx-auto h-24 w-24 text-accent-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="mt-3 text-xs font-semibold">{{ __('Product image not available') }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="mb-8">
        <div class="flex flex-col gap-5 border-b border-slate-100 pb-6 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="break-words text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ $product->name }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs">
                    @if ($ratingSummary->review_count > 0)
                        <a href="#reviews" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1.5 font-bold text-amber-700 ring-1 ring-inset ring-amber-200 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.951.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.448a1 1 0 00-.364 1.118l1.285 3.957a1 1 0 01-1.536 1.28L10.69 15.73a1 1 0 00-1.175 0l-3.366 2.457a1 1 0 01-1.537-1.28l1.286-3.957a1 1 0 00-.364-1.118L2.367 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.286-3.957z"/></svg>
                            {{ number_format((float) $ratingSummary->average_rating, 1) }}
                        </a>
                        <span class="font-medium text-slate-500">{{ $ratingSummary->review_count }} {{ __('reviews') }}</span>
                    @else
                        <span class="font-medium text-slate-500">{{ __('No reviews yet') }}</span>
                    @endif
                    <span class="text-slate-300" aria-hidden="true">&bull;</span>
                    <span class="font-semibold text-slate-600">{{ __('Seller') }}: <span class="font-bold text-brand-700">{{ $product->vendor->business_name }}</span></span>
                </div>
            </div>
            <div class="shrink-0 sm:text-right">
                <p class="text-3xl font-black text-slate-900">৳ {{ number_format($product->price, 2) }}</p>
                <x-ui.badge :tone="$stock > 0 ? 'success' : 'danger'" class="mt-2">{{ $stock > 0 ? $stock.' '.__('in stock') : __('Out of stock') }}</x-ui.badge>
            </div>
        </div>

        <div class="grid gap-8 py-7 lg:grid-cols-2">
            <section aria-labelledby="description-title">
                <h2 id="description-title" class="ui-section-title mb-3">{{ __('Description') }}</h2>
                <p class="whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $product->description ?: __('No description has been provided for this product.') }}</p>
            </section>
            <section aria-labelledby="details-title">
                <h2 id="details-title" class="ui-section-title mb-3">{{ __('Product details') }}</h2>
                <dl class="grid grid-cols-1 gap-3 text-sm min-[420px]:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4"><dt class="font-bold text-slate-800">{{ __('Brand') }}</dt><dd class="mt-1 text-slate-500">{{ $product->brand?->name ?? '—' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-4"><dt class="font-bold text-slate-800">{{ __('Category') }}</dt><dd class="mt-1 text-slate-500">{{ $product->category?->name ?? '—' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-4"><dt class="font-bold text-slate-800">{{ __('Seller') }}</dt><dd class="mt-1 break-words text-slate-500">{{ $product->vendor->business_name }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-4"><dt class="font-bold text-slate-800">{{ __('Availability') }}</dt><dd class="mt-1 text-slate-500">{{ $stock > 0 ? __('In stock') : __('Out of stock') }}</dd></div>
                </dl>
            </section>
        </div>

        <section id="reviews" class="scroll-mt-28 border-t border-slate-100 pt-7" aria-labelledby="reviews-title">
            <div class="mb-5">
                <h2 id="reviews-title" class="text-xl font-extrabold text-slate-900">{{ __('Customer reviews') }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    @if ($ratingSummary->review_count > 0)
                        {{ number_format((float) $ratingSummary->average_rating, 1) }}/5 {{ __('from') }} {{ $ratingSummary->review_count }} {{ __('reviews') }}
                    @else
                        {{ __('Be the first to review this product.') }}
                    @endif
                </p>
            </div>

            @auth
                <form method="POST" action="{{ route('reviews.store') }}" class="mb-6 rounded-2xl bg-slate-50 p-4 sm:p-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <h3 class="mb-4 text-base font-bold text-slate-800">{{ __('Write a review') }}</h3>
                    <div class="grid gap-4 sm:grid-cols-[10rem_minmax(0,1fr)] sm:items-start">
                        <x-form.select name="rating" :label="__('Rating')" required>
                            @for ($rating = 5; $rating >= 1; $rating--)
                                <option value="{{ $rating }}" @selected((int) old('rating', 5) === $rating)>{{ $rating }}/5</option>
                            @endfor
                        </x-form.select>
                        <x-form.textarea name="comment" :label="__('Comment')" rows="4" maxlength="2000">{{ old('comment') }}</x-form.textarea>
                    </div>
                    <x-ui.button type="submit" class="mt-5">{{ __('Submit review') }}</x-ui.button>
                </form>
            @else
                <div class="mb-6 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                    <a href="{{ route('login') }}" class="ui-link">{{ __('Sign in') }}</a> {{ __('to write a review.') }}
                </div>
            @endauth

            <div class="space-y-3">
                @forelse ($reviews as $review)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-bold text-slate-800">{{ $review->user?->name ?? __('Customer') }}</h3>
                            <span class="inline-flex items-center gap-0.5" role="img" aria-label="{{ __(':rating out of 5 stars', ['rating' => $review->rating]) }}">
                                @for ($star = 1; $star <= 5; $star++)
                                    <svg class="h-4 w-4 {{ $star <= $review->rating ? 'text-amber-400' : 'text-slate-300' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.951.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.448a1 1 0 00-.364 1.118l1.285 3.957a1 1 0 01-1.536 1.28L10.69 15.73a1 1 0 00-1.175 0l-3.366 2.457a1 1 0 01-1.537-1.28l1.286-3.957a1 1 0 00-.364-1.118L2.367 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.286-3.957z"/></svg>
                                @endfor
                            </span>
                        </div>
                        <p class="whitespace-pre-line break-words text-sm leading-6 text-slate-600">{{ $review->comment ?: __('No comment provided.') }}</p>
                        <p class="mt-2 text-[11px] text-slate-400"><time datetime="{{ $review->created_at?->toIso8601String() }}">{{ $review->created_at?->format('d M Y') }}</time></p>
                    </article>
                @empty
                    <x-ui.card :padding="false">
                        <x-ui.empty-state :title="__('No reviews have been submitted yet')" :description="__('Share your experience with this product.')" />
                    </x-ui.card>
                @endforelse
            </div>
            <div class="ui-pagination mt-5">{{ $reviews->links() }}</div>
        </section>
    </x-ui.card>

    <div class="sticky bottom-24 z-30 flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white/95 p-3 shadow-xl backdrop-blur-md sm:flex-row sm:items-center sm:justify-between sm:p-4 md:bottom-4">
        <div class="flex items-center self-start rounded-full bg-slate-100 p-1 sm:self-auto" role="group" aria-label="{{ __('Quantity') }}">
            <button type="button" @click="if (quantity > 1) quantity--" class="ui-icon-btn h-11 w-11 bg-white shadow-sm" aria-label="{{ __('Decrease quantity') }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M5 12h14"/></svg>
            </button>
            <span class="w-10 text-center text-sm font-extrabold text-slate-800" x-text="quantity" aria-live="polite"></span>
            <button type="button" @click="if (quantity < maxQuantity) quantity++" class="ui-icon-btn h-11 w-11 bg-white shadow-sm" aria-label="{{ __('Increase quantity') }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('cart.store') }}" class="w-full sm:max-w-md">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" :value="quantity">
            <x-ui.button type="submit" :disabled="$stock < 1" class="w-full">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 11.6a2 2 0 002 1.6h7.7a2 2 0 002-1.6L21 7H6M9 20a1 1 0 11-2 0 1 1 0 012 0zm10 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                <span>{{ $stock > 0 ? __('Add to cart') : __('Out of stock') }}</span>
                <span class="opacity-80" aria-hidden="true">&bull;</span>
                <span x-text="'৳ ' + ({{ $product->price }} * quantity).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
            </x-ui.button>
        </form>
    </div>
</div>
@endsection
