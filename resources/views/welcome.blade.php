@extends('layouts.frontend')
@section('title', $siteSettings->site_name)

@section('content')
    <section class="relative isolate overflow-hidden rounded-[2rem] bg-ink-900 px-6 py-14 text-white shadow-xl sm:px-10 sm:py-20 lg:px-16 lg:py-24">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-accent-600/40 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 left-1/3 h-72 w-72 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-200">
                <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                {{ __('Modern commerce platform') }}
            </span>
            <h1 class="mt-7 max-w-2xl text-4xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                {{ __('A better way to discover, shop and grow.') }}
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
                {{ __('Explore a thoughtfully curated catalog, enjoy a seamless checkout and keep every order in one place.') }}
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                <x-ui.button :href="route('products.index')" variant="primary">
                    {{ __('Explore products') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
                </x-ui.button>
                @auth
                    <x-ui.button :href="route('customer.dashboard')" variant="outline" class="border-white/20 bg-white/5 text-white hover:bg-white/10">
                        {{ __('Go to dashboard') }}
                    </x-ui.button>
                @else
                    @if (Route::has('register'))
                        <x-ui.button :href="route('register')" variant="outline" class="border-white/20 bg-white/5 text-white hover:bg-white/10">
                            {{ __('Create an account') }}
                        </x-ui.button>
                    @endif
                @endauth
            </div>
        </div>
    </section>

    <div class="mt-8 grid gap-5 md:grid-cols-3">
        @foreach ([
            ['title' => __('Curated catalog'), 'description' => __('Find products through clear categories, useful filters and detailed product pages.'), 'icon' => 'M20 7l-8-4-8 4m16 0v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4'],
            ['title' => __('Simple checkout'), 'description' => __('Review your cart, choose a delivery address and complete your order with confidence.'), 'icon' => 'M3 3h2l2.4 12.2a2 2 0 002 1.6h7.7a2 2 0 002-1.6L21 7H6M9 20a1 1 0 11-2 0 1 1 0 012 0zm10 0a1 1 0 11-2 0 1 1 0 012 0z'],
            ['title' => __('Order visibility'), 'description' => __('Follow payments, shipment progress and order history from your customer account.'), 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ] as $feature)
            <x-ui.card class="p-6">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/></svg>
                </span>
                <h2 class="mt-5 text-lg font-bold text-slate-900">{{ $feature['title'] }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $feature['description'] }}</p>
            </x-ui.card>
        @endforeach
    </div>
@endsection
