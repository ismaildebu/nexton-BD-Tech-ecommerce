@extends('layouts.frontend')
@section('title', __('My Cart - :store', ['store' => $siteSettings->site_name]))

@section('content')
<div class="ui-page-narrow">
    <x-ui.page-header :title="__('My cart')" :description="__('Review your items and delivery details before checkout.')">
        <x-slot:action>
            <x-ui.button :href="route('products.index')" variant="outline">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
                {{ __('Continue shopping') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    @if ($couponError)
        <x-ui.alert tone="error" class="mb-6">{{ $couponError }}</x-ui.alert>
    @endif

    @if (! $cart || $cart->items->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state :title="__('Your cart is currently empty')" :description="__('Explore trending tech, gadgets, and products from verified merchants.')">
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 11.2a2 2 0 002 1.6h7.9a2 2 0 002-1.6L21 7H6M10 21a1 1 0 11-2 0 1 1 0 012 0zm8 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                </x-slot:icon>
                <x-slot:action>
                    <x-ui.button :href="route('products.index')">
                        {{ __('Start shopping') }}
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        @php
            $hasBlockingItems = false;
            foreach ($cart->items as $item) {
                $stock = $item->product?->inventory?->quantity;
                if (! $item->product || ! $item->product->isPurchasable() || $stock === null || $stock < $item->quantity || $item->quantity > 999) {
                    $hasBlockingItems = true;
                }
            }
        @endphp

        <section class="mb-7" aria-labelledby="cart-items-title">
            <h2 id="cart-items-title" class="ui-section-title mb-4">{{ __('Cart items') }} <span class="font-semibold text-slate-400">({{ $cart->items->count() }})</span></h2>
            <div class="space-y-4">
                @foreach ($cart->items as $item)
                    @php
                        $prod = $item->product;
                        $stock = $prod?->inventory?->quantity;
                        $available = (bool) $prod?->isPurchasable();
                        $canIncrease = $available && $stock !== null && $item->quantity < min($stock, 999);
                        $canDecrease = $available && $stock !== null && $stock >= 1 && $item->quantity > 1;
                    @endphp
                    <x-ui.card :padding="false" class="p-4 sm:p-5">
                        <article class="flex items-start gap-4">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-50 to-accent-50 text-accent-500 sm:h-24 sm:w-24" aria-hidden="true">
                                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="break-words text-sm font-bold leading-6 text-slate-900 sm:text-base">{{ $prod->name ?? __('Product') }}</h3>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $prod?->category?->name ?? __('General Tech') }}</p>
                                        @if (! $available)
                                            <p class="mt-2 text-xs font-semibold text-rose-600">{{ __('This product is no longer available.') }}</p>
                                        @elseif ($stock === null || $stock < $item->quantity)
                                            <p class="mt-2 text-xs font-semibold text-rose-600">{{ __('Only :quantity available.', ['quantity' => $stock ?? 0]) }}</p>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ui-icon-btn h-11 w-11 hover:bg-rose-50 hover:text-rose-600" aria-label="{{ __('Remove :product from cart', ['product' => $prod->name ?? __('Product')]) }}" title="{{ __('Remove item') }}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-sm font-extrabold text-slate-900">৳ {{ number_format($prod->price ?? 0, 2) }}</p>
                                    <div class="flex items-center rounded-full bg-slate-100 p-1" role="group" aria-label="{{ __('Quantity for :product', ['product' => $prod->name ?? __('Product')]) }}">
                                        <form method="POST" action="{{ route('cart.update', $item) }}" class="inline-flex">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ max(1, min($item->quantity - 1, $stock ?? 1, 999)) }}">
                                            <button type="submit" @disabled(! $canDecrease) class="ui-icon-btn h-11 w-11 bg-white shadow-sm disabled:cursor-not-allowed disabled:opacity-40" aria-label="{{ __('Decrease quantity') }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M5 12h14"/></svg>
                                            </button>
                                        </form>
                                        <span class="w-9 text-center text-xs font-bold text-slate-800" aria-live="polite">{{ $item->quantity }}</span>
                                        <form method="POST" action="{{ route('cart.update', $item) }}" class="inline-flex">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                            <button type="submit" @disabled(! $canIncrease) class="ui-icon-btn h-11 w-11 bg-white shadow-sm disabled:cursor-not-allowed disabled:opacity-40" aria-label="{{ __('Increase quantity') }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </x-ui.card>
                @endforeach
            </div>
        </section>

        <x-ui.card class="mb-6">
            @if ($coupon)
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">{{ __('Applied coupon') }}</p>
                        <x-ui.badge tone="accent" class="mt-1.5">{{ $coupon->code }}</x-ui.badge>
                    </div>
                    <form method="POST" action="{{ route('cart.coupon.remove') }}">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="outline">{{ __('Remove coupon') }}</x-ui.button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('cart.coupon.apply') }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                    @csrf
                    <x-form.input name="code" :label="__('Discount code')" :value="old('code')" required maxlength="64" :placeholder="__('Enter discount code')" class="uppercase placeholder:normal-case" />
                    <x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">{{ __('Apply') }}</x-ui.button>
                </form>
            @endif
        </x-ui.card>

        <x-ui.card class="mb-6">
            <h2 class="ui-section-title mb-4">{{ __('Order summary') }}</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between gap-4 text-slate-500">
                    <dt>{{ __('Subtotal') }}</dt>
                    <dd class="font-bold text-slate-800">৳ {{ number_format($subtotal, 2) }}</dd>
                </div>
                @if ($coupon)
                    <div class="flex items-center justify-between gap-4 text-emerald-700">
                        <dt>{{ __('Discount (:code)', ['code' => $coupon->code]) }}</dt>
                        <dd class="font-bold">−৳ {{ number_format($discount, 2) }}</dd>
                    </div>
                @endif
                <div class="flex items-center justify-between gap-4 text-slate-500">
                    <dt>{{ __('Delivery & shipping') }}</dt>
                    <dd class="font-bold text-emerald-700">{{ __('Free') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-4">
                    <dt class="font-bold text-slate-900">{{ __('Total') }}</dt>
                    <dd class="text-xl font-black text-slate-900">৳ {{ number_format($total, 2) }}</dd>
                </div>
            </dl>
        </x-ui.card>

        @if ($hasBlockingItems)
            <x-ui.alert tone="warning" class="mb-6">{{ __('Update or remove unavailable items before checkout.') }}</x-ui.alert>
        @endif

        @if ($addresses->isEmpty())
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('Add a delivery address before checkout')" :description="__('Choose where you would like your order delivered.')">
                    <x-slot:action>
                        <x-ui.button :href="route('addresses.create')">{{ __('Add address') }}</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <x-ui.card>
                <form method="POST" action="{{ route('checkout.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">

                    <x-form.select name="address_id" :label="__('Delivery address')" required>
                        @foreach ($addresses as $address)
                            <option value="{{ $address->id }}" @selected((int) old('address_id', $addresses->firstWhere('is_default', true)?->id ?? $addresses->first()->id) === $address->id)>
                                {{ $address->full_name }} — {{ $address->address_line }}, {{ $address->district }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-ui.button type="submit" :disabled="$hasBlockingItems" class="w-full">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
                        <span>{{ __('Checkout') }}</span>
                        <span class="opacity-80" aria-hidden="true">&bull;</span>
                        <span>৳ {{ number_format($total, 2) }}</span>
                    </x-ui.button>
                </form>
            </x-ui.card>
        @endif
    @endif
</div>
@endsection
