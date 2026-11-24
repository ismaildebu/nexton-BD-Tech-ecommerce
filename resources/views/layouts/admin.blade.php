<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Control panel')) · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900" x-data="{ mobileOpen: false }" x-effect="document.body.style.overflow = mobileOpen ? 'hidden' : ''" @ui:dismiss.window="if ($event.target.id === 'admin-navigation') { mobileOpen = false; $nextTick(() => $refs.adminDrawerOpen?.focus()); }">
    <a href="#admin-main" class="sr-only z-[100] rounded-lg bg-white px-4 py-3 font-semibold focus:not-sr-only focus:fixed focus:left-4 focus:top-4">{{ __('Skip to content') }}</a>

    <div class="flex min-h-screen">
        <div x-show="mobileOpen" @click="mobileOpen = false; $nextTick(() => $refs.adminDrawerOpen.focus())" class="fixed inset-0 z-40 bg-ink-900/60 backdrop-blur-sm md:hidden" x-cloak aria-hidden="true"></div>

        <aside id="admin-navigation" x-ref="adminDrawer" tabindex="-1" :role="mobileOpen ? 'dialog' : undefined" :aria-modal="mobileOpen ? 'true' : undefined" aria-label="{{ __('Admin navigation') }}" :class="mobileOpen ? 'visible translate-x-0' : 'invisible -translate-x-full md:visible md:translate-x-0'" class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 flex-col border-r border-white/10 bg-ink-900 text-slate-300 transition-transform duration-200 md:static">
            <div class="flex h-20 items-center justify-between border-b border-white/10 px-5">
                <x-ui.brand inverse :href="route('admin.dashboard')" />
                <button x-ref="adminDrawerClose" type="button" @click="mobileOpen = false; $nextTick(() => $refs.adminDrawerOpen.focus())" class="ui-icon-btn text-slate-400 hover:bg-white/10 hover:text-white md:hidden" aria-label="{{ __('Close navigation') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @php
                $groups = [
                    __('Navigation') => [
                        ['label' => __('Dashboard'), 'url' => route('admin.dashboard'), 'pattern' => 'admin.dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                    ],
                    __('E-commerce') => [
                        ['label' => __('Products'), 'url' => route('admin.products.index'), 'pattern' => 'admin.products.*', 'icon' => 'M20 7l-8-4-8 4m16 0v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4'],
                        ['label' => __('Inventory'), 'url' => route('admin.inventory.index'), 'pattern' => 'admin.inventory.*', 'icon' => 'M4 7h16M4 7l2-4h12l2 4M5 7v14h14V7M9 11h6'],
                        ['label' => __('Categories'), 'url' => route('admin.categories.index'), 'pattern' => 'admin.categories.*', 'icon' => 'M3 7h6l2 2h10v10H3V7z'],
                        ['label' => __('Brands'), 'url' => route('admin.brands.index'), 'pattern' => 'admin.brands.*', 'icon' => 'M3 12h18M12 3v18M5 5l14 14M19 5L5 19'],
                        ['label' => __('Orders'), 'url' => route('admin.orders.index'), 'pattern' => 'admin.orders.*', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                        ['label' => __('Payments'), 'url' => route('admin.payments.index'), 'pattern' => 'admin.payments.*', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                        ['label' => __('Shipping'), 'url' => route('admin.shipping.index'), 'pattern' => 'admin.shipping.*', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                        ['label' => __('Coupons'), 'url' => route('admin.coupons.index'), 'pattern' => 'admin.coupons.*', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                    ],
                    __('Marketplace & partners') => [
                        ['label' => __('Customers'), 'url' => route('admin.customers.index'), 'pattern' => 'admin.customers.*', 'icon' => 'M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M14 4a4 4 0 010 8M10 11a4 4 0 100-8 4 4 0 000 8z'],
                        ['label' => __('Vendors'), 'url' => route('admin.vendors.index'), 'pattern' => 'admin.vendors.*', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1'],
                        ['label' => __('Commissions'), 'url' => route('admin.commissions.index'), 'pattern' => 'admin.commissions.*', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => __('Settlements'), 'url' => route('admin.settlements.index'), 'pattern' => 'admin.settlements.*', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                        ['label' => __('Subscriptions'), 'url' => route('admin.subscriptions.index'), 'pattern' => 'admin.subscriptions.*', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                    ],
                    __('Content & system') => [
                        ['label' => __('Reviews'), 'url' => route('admin.reviews.index'), 'pattern' => 'admin.reviews.*', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                        ['label' => __('CMS pages'), 'url' => route('admin.cms.index'), 'pattern' => 'admin.cms.*', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
                        ['label' => __('Notifications'), 'url' => route('admin.notifications.index'), 'pattern' => 'admin.notifications.*', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1'],
                        ['label' => __('Wishlist'), 'url' => route('admin.wishlist.index'), 'pattern' => 'admin.wishlist.*', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                    ],
                ];
            @endphp

            <nav class="ui-scrollbar flex-1 space-y-5 overflow-y-auto px-3 py-5">
                @foreach ($groups as $label => $items)
                    <div>
                        <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $label }}</p>
                        <div class="space-y-1">
                            @foreach ($items as $item)
                                @php($active = request()->routeIs($item['pattern']))
                                <a href="{{ $item['url'] }}" @if ($active) aria-current="page" @endif class="{{ $active ? 'bg-accent-600 text-white shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }} ui-sidebar-link">
                                    <svg class="h-4 w-4 shrink-0 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                                    <span>{{ $item['label'] }}</span>
                                    @if ($item['pattern'] === 'admin.dashboard')
                                        <span class="ml-auto rounded-full bg-emerald-400/15 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-emerald-300">{{ __('Live') }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="mb-3 flex items-center gap-3 rounded-xl bg-white/5 p-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-500 text-sm font-bold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name ?? __('Admin') }}</p>
                        <p class="truncate text-xs text-slate-400">{{ __('Administrator') }}</p>
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
                    <button x-ref="adminDrawerOpen" type="button" @click="mobileOpen = true; $nextTick(() => $refs.adminDrawer.focus())" class="ui-icon-btn md:hidden" aria-label="{{ __('Open navigation') }}" aria-controls="admin-navigation" :aria-expanded="mobileOpen.toString()">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="md:hidden"><x-ui.brand compact :href="route('admin.dashboard')" /></div>
                    <div class="hidden md:block">
                        <p class="text-sm font-bold text-slate-900">{{ __('Control panel') }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">@yield('title', __('Admin workspace'))</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="ui-btn ui-btn-outline ui-btn-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span class="hidden sm:inline">{{ __('View store') }}</span>
                    </a>
                    <span class="hidden h-9 w-9 items-center justify-center rounded-full bg-accent-100 text-xs font-bold text-accent-700 sm:flex">{{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                </div>
            </header>

            <main id="admin-main" class="mx-auto w-full max-w-[100rem] flex-1 px-4 py-7 sm:px-6 md:py-9 lg:px-8">
                @include('layouts.partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
