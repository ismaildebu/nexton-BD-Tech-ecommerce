<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Vendor portal')) · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900" x-data="{ mobileOpen: false }" x-effect="document.body.style.overflow = mobileOpen ? 'hidden' : ''" @ui:dismiss.window="if ($event.target.id === 'vendor-navigation') { mobileOpen = false; $nextTick(() => $refs.vendorDrawerOpen?.focus()); }">
    <a href="#vendor-main" class="sr-only z-[100] rounded-lg bg-white px-4 py-3 font-semibold text-slate-900 focus:not-sr-only focus:fixed focus:left-4 focus:top-4">{{ __('Skip to content') }}</a>

    <div class="flex min-h-screen">
        <div x-show="mobileOpen" @click="mobileOpen = false; $nextTick(() => $refs.vendorDrawerOpen.focus())" class="fixed inset-0 z-40 bg-ink-900/60 backdrop-blur-sm md:hidden" x-cloak aria-hidden="true"></div>

        <aside id="vendor-navigation" x-ref="vendorDrawer" tabindex="-1" :role="mobileOpen ? 'dialog' : undefined" :aria-modal="mobileOpen ? 'true' : undefined" aria-label="{{ __('Vendor navigation') }}" :class="mobileOpen ? 'visible translate-x-0' : 'invisible -translate-x-full md:visible md:translate-x-0'" class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 flex-col border-r border-white/10 bg-ink-900 text-slate-300 transition-transform duration-200 md:static">
            <div class="flex h-20 items-center justify-between border-b border-white/10 px-5">
                <x-ui.brand inverse :href="route('vendor.dashboard')" />
                <button x-ref="vendorDrawerClose" type="button" @click="mobileOpen = false; $nextTick(() => $refs.vendorDrawerOpen.focus())" class="ui-icon-btn text-slate-400 hover:bg-white/10 hover:text-white md:hidden" aria-label="{{ __('Close navigation') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @php
                $vendor = $vendor ?? auth()->user()?->vendor
                    ?? \App\Modules\Vendor\Models\Vendor::accessibleTo(auth()->user())->first();
                $links = [
                    ['label' => __('Dashboard'), 'url' => route('vendor.dashboard'), 'active' => request()->routeIs('vendor.dashboard'), 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
                    ['label' => __('Products'), 'url' => route('vendor.products.index'), 'active' => request()->routeIs('vendor.products.*'), 'icon' => 'M20 7l-8-4-8 4m16 0v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4'],
                    ['label' => __('Inventory'), 'url' => route('vendor.inventory.index'), 'active' => request()->routeIs('vendor.inventory.*'), 'icon' => 'M4 7h16M4 7l2-4h12l2 4M5 7v14h14V7M9 11h6'],
                    ['label' => __('Orders'), 'url' => route('vendor.orders.index'), 'active' => request()->routeIs('vendor.orders.*'), 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                ];

                if ($vendor) {
                    $links[] = ['label' => __('Documents'), 'url' => route('vendor.documents.index', $vendor), 'active' => request()->routeIs('vendor.documents.*'), 'icon' => 'M9 12h6m-6 4h6M7 3h7l5 5v13H7V3z'];
                    $links[] = ['label' => __('Bank accounts'), 'url' => route('vendor.bank-accounts.index', $vendor), 'active' => request()->routeIs('vendor.bank-accounts.*'), 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'];

                    if (auth()->id() === $vendor->owner_user_id) {
                        $links[] = ['label' => __('Staff'), 'url' => route('vendor.staff.index', $vendor), 'active' => request()->routeIs('vendor.staff.*'), 'icon' => 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M14 4a4 4 0 010 8M10 11a4 4 0 100-8 4 4 0 000 8z'];
                        $links[] = ['label' => __('Business profile'), 'url' => route('vendor.settings.edit', $vendor), 'active' => request()->routeIs('vendor.settings.*'), 'icon' => 'M4 4h16v16H4V4zm5 5h6m-6 4h6'];
                        $links[] = ['label' => __('Registration status'), 'url' => route('vendor.registration.status'), 'active' => request()->routeIs('vendor.registration.status'), 'icon' => 'M9 12l2 2 4-4M12 3l9 4v5c0 5-3.5 8-9 9-5.5-1-9-4-9-9V7l9-4z'];
                        $links[] = ['label' => __('Commissions'), 'url' => route('commissions.index'), 'active' => request()->routeIs('commissions.*'), 'icon' => 'M12 3v18M17 7H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'];
                        $links[] = ['label' => __('Settlements'), 'url' => route('settlements.index'), 'active' => request()->routeIs('settlements.*'), 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'];
                        $links[] = ['label' => __('Subscriptions'), 'url' => route('subscriptions.index'), 'active' => request()->routeIs('subscriptions.*'), 'icon' => 'M4 7h16M4 17h16M8 3v18M16 3v18'];
                    }
                } else {
                    $links[] = ['label' => __('Register as vendor'), 'url' => route('vendor.register.create'), 'active' => request()->routeIs('vendor.register.*'), 'icon' => 'M12 4v16m8-8H4'];
                }
            @endphp

            <nav class="ui-scrollbar flex-1 space-y-1 overflow-y-auto p-3">
                <p class="mb-3 px-3 pt-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ __('Workspace') }}</p>
                @foreach ($links as $item)
                    <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif class="{{ $item['active'] ? 'bg-accent-600 text-white shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }} ui-sidebar-link">
                        <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="mb-3 flex items-center gap-3 rounded-xl bg-white/5 p-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-500 text-sm font-bold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-400">{{ $vendor?->business_name ?? __('Merchant account') }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ui-sidebar-link w-full text-slate-400 hover:bg-rose-500/10 hover:text-rose-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('Sign out') }}
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur md:px-8">
                <div class="flex items-center gap-3">
                    <button x-ref="vendorDrawerOpen" type="button" @click="mobileOpen = true; $nextTick(() => $refs.vendorDrawer.focus())" class="ui-icon-btn md:hidden" aria-label="{{ __('Open navigation') }}" aria-controls="vendor-navigation" :aria-expanded="mobileOpen.toString()">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="md:hidden"><x-ui.brand compact :href="route('vendor.dashboard')" /></div>
                    <div class="hidden md:block">
                        <p class="text-sm font-bold text-slate-900">{{ __('Vendor portal') }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">@yield('title', __('Merchant workspace'))</p>
                    </div>
                </div>
                <a href="{{ route('products.index') }}" class="ui-btn ui-btn-outline ui-btn-sm" aria-label="{{ __('View store') }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span class="hidden sm:inline">{{ __('View store') }}</span>
                </a>
            </header>

            <main id="vendor-main" class="mx-auto w-full max-w-7xl flex-1 px-4 py-7 sm:px-6 md:py-9 lg:px-8">
                @include('layouts.partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
