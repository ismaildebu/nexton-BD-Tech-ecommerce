<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $siteSettings->site_name)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme')
</head>
<body class="site-body min-h-screen antialiased flex flex-col" data-header-style="{{ $siteSettings->header_style }}" data-footer-style="{{ $siteSettings->footer_style }}" x-data="{ mobileDrawer: false }" x-effect="document.body.style.overflow = mobileDrawer ? 'hidden' : ''" @resize.window="if (innerWidth >= 1024) mobileDrawer = false" @ui:dismiss.window="if ($event.target.id === 'storefront-navigation') { mobileDrawer = false; $nextTick(() => $refs.storeDrawerOpen?.focus()); }">
    <a href="#storefront-main" class="sr-only z-[100] rounded-lg bg-white px-4 py-3 font-semibold text-slate-900 focus:not-sr-only focus:fixed focus:left-4 focus:top-4">{{ __('Skip to content') }}</a>
    @if ($siteSettings->announcement)
        <div class="site-announcement px-4 py-2 text-center text-[11px] font-medium text-white">
            <span>{{ $siteSettings->announcement }}</span>
            <a href="{{ route('products.index') }}" class="ml-2 underline text-brand-300 hover:text-brand-200">{{ __('Shop now') }}</a>
        </div>
    @endif

    <!-- Main Navigation Header (Image 2 ESTORE Style) -->
    <header class="site-header sticky top-0 z-40 backdrop-blur-md transition shadow-xs">
        <div class="ui-container">
            <div class="site-header-inner flex items-center justify-between gap-4">
                <!-- Left Nav Links (Desktop) -->
                <nav class="store-desktop-nav hidden lg:flex items-center gap-7 text-xs font-semibold text-slate-600">
                    <a href="{{ route('products.index') }}" class="hover:text-brand-500 transition {{ request()->routeIs('products.*') ? 'text-brand-500' : '' }}">{{ __('Catalog') }}</a>
                    <a href="{{ url('/') }}#popular" class="hover:text-brand-500 transition">{{ __('Popular') }}</a>
                    <a href="{{ url('/') }}#categories" class="hover:text-brand-500 transition">{{ __('Categories') }}</a>
                    <a href="{{ url('/') }}#vendors" class="hover:text-brand-500 transition">{{ __('Vendors') }}</a>
                    <a href="{{ route('customer.dashboard') }}" class="hover:text-brand-500 transition">{{ __('Support') }}</a>
                </nav>

                <!-- Mobile Hamburger Button -->
                <button x-ref="storeDrawerOpen" type="button" @click="mobileDrawer = true; $nextTick(() => $refs.storeDrawerClose.focus())" class="store-mobile-toggle ui-icon-btn lg:hidden" aria-label="{{ __('Open navigation') }}" aria-controls="storefront-navigation" :aria-expanded="mobileDrawer.toString()">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Center Logo (Bold Minimalist ESTORE style) -->
                <div class="store-logo flex min-w-0 shrink-0 items-center">
                    <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2">
                        @if ($siteSettings->logo_url)
                            <img src="{{ $siteSettings->logo_url }}" alt="" class="h-10 w-auto max-w-24 object-contain">
                        @endif
                        <span class="max-w-32 truncate text-2xl font-black tracking-tighter text-ink-900 sm:max-w-48">{{ $siteSettings->site_name }}</span>
                    </a>
                </div>

                <!-- Right Action Icons (Image 2: Search, Wishlist, Cart, Profile) -->
                <div class="store-actions flex items-center gap-2 sm:gap-4">
                    <!-- Search Button / Bar -->
                    <a href="{{ route('products.index') }}" class="ui-icon-btn" aria-label="{{ __('Search products') }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </a>

                    <!-- Wishlist Icon -->
                    <a href="{{ route('wishlist.index') }}" class="ui-icon-btn relative" aria-label="{{ __('Wishlist') }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </a>

                    <!-- Cart Icon with badge -->
                    <a href="{{ route('cart.index') }}" class="ui-icon-btn relative" aria-label="{{ __('Shopping cart') }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.4 12.2a2 2 0 002 1.6h7.7a2 2 0 002-1.6L21 7H6M9 20a1 1 0 11-2 0 1 1 0 012 0zm10 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                        </svg>
                    </a>

                    <!-- Profile / Auth -->
                    @auth
                        <div class="relative flex items-center gap-2 pl-2 border-l border-slate-200">
                            <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('customer.dashboard') }}" class="flex items-center gap-2 text-xs font-semibold text-slate-700 hover:text-brand-500">
                                <div class="w-8 h-8 rounded-full bg-ink-900 text-white flex items-center justify-center font-bold text-xs">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="hidden md:inline">{{ auth()->user()->name }}</span>
                            </a>
                        </div>
                    @else
                        <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                            <a href="{{ route('login') }}" class="text-xs font-semibold px-3 py-1.5 text-slate-700 hover:text-brand-500 transition">
                                {{ __('Sign In') }}
                            </a>
                            <a href="{{ route('register') }}" class="hidden sm:inline-flex text-xs font-bold px-4 py-2 rounded-full bg-ink-900 text-white hover:bg-black transition shadow-sm">
                                {{ __('Join') }}
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

    </header>

    <!-- Mobile Drawer Menu -->
    <div x-show="mobileDrawer" @click="mobileDrawer = false; $nextTick(() => $refs.storeDrawerOpen.focus())" class="fixed inset-0 z-50 bg-ink-900/60 backdrop-blur-sm lg:hidden" x-cloak aria-hidden="true"></div>
    <div id="storefront-navigation" x-ref="storeDrawer" x-show="mobileDrawer" role="dialog" aria-modal="true" aria-label="{{ __('Store navigation') }}" tabindex="-1" class="fixed inset-y-0 left-0 z-50 flex w-[min(20rem,88vw)] flex-col justify-between overflow-y-auto overscroll-contain bg-white px-6 pt-6 pb-[max(1.5rem,env(safe-area-inset-bottom))] shadow-2xl ui-scrollbar lg:hidden" x-cloak>
        <div class="shrink-0">
            <div class="flex items-center justify-between mb-8">
                <x-ui.brand compact />
                <button x-ref="storeDrawerClose" type="button" @click="mobileDrawer = false; $nextTick(() => $refs.storeDrawerOpen.focus())" class="ui-icon-btn" aria-label="{{ __('Close navigation') }}">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-4 text-sm font-semibold text-slate-700">
                <a href="{{ url('/') }}" class="block py-2 hover:text-brand-500">{{ __('Home') }}</a>
                <a href="{{ route('products.index') }}" class="block py-2 hover:text-brand-500">{{ __('Catalog & Products') }}</a>
                <a href="{{ route('cart.index') }}" class="block py-2 hover:text-brand-500">{{ __('Cart') }}</a>
                <a href="{{ route('wishlist.index') }}" class="block py-2 hover:text-brand-500">{{ __('Wishlist') }}</a>
                @auth
                    <a href="{{ route('customer.dashboard') }}" class="block py-2 hover:text-brand-500">{{ __('My Dashboard') }}</a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="block py-2 text-accent-600 font-bold">{{ __('Admin Panel') }}</a>
                    @endif
                @endauth
            </div>
        </div>
        <div class="shrink-0 pt-6 border-t border-slate-100">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-center py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200">
                        {{ __('Sign out') }}
                    </button>
                </form>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="text-center py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold">{{ __('Sign in') }}</a>
                    <a href="{{ route('register') }}" class="text-center py-2.5 rounded-xl bg-brand-500 text-white text-xs font-bold">{{ __('Create account') }}</a>
                </div>
            @endauth
        </div>
    </div>

    <!-- Main Content Area -->
    <main id="storefront-main" class="flex-1 pb-28 md:pb-16">
        @if (session('status') || session('error') || $errors->any())
            <div class="ui-container pt-5">
                @include('layouts.partials.flash')
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Floating Mobile Bottom Navigation Bar (Image 4 Style) -->
    <nav aria-label="{{ __('Mobile navigation') }}" class="mobile-store-nav md:hidden fixed bottom-4 inset-x-4 z-40 backdrop-blur-lg border rounded-full py-2 px-6 shadow-xl flex items-center justify-between">
        <a href="{{ url('/') }}" aria-label="{{ __('Home') }}" @if (request()->is('/')) aria-current="page" @endif class="flex flex-col items-center gap-0.5 {{ request()->is('/') ? 'text-brand-500' : 'text-slate-400' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ request()->is('/') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/30' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('wishlist.index') }}" aria-label="{{ __('Wishlist') }}" @if (request()->routeIs('wishlist.*')) aria-current="page" @endif class="flex flex-col items-center gap-0.5 {{ request()->routeIs('wishlist.*') ? 'text-brand-500' : 'text-slate-400' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ request()->routeIs('wishlist.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/30' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('cart.index') }}" aria-label="{{ __('Cart') }}" @if (request()->routeIs('cart.*')) aria-current="page" @endif class="flex flex-col items-center gap-0.5 {{ request()->routeIs('cart.*') ? 'text-brand-500' : 'text-slate-400' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ request()->routeIs('cart.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/30' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.4 12.2a2 2 0 002 1.6h7.7a2 2 0 002-1.6L21 7H6M9 20a1 1 0 11-2 0 1 1 0 012 0zm10 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                </svg>
            </div>
        </a>

        <a href="{{ auth()->check() ? (auth()->user()->is_admin ? route('admin.dashboard') : route('customer.dashboard')) : route('login') }}" aria-label="{{ __('Account') }}" @if (request()->routeIs('customer.*') || request()->routeIs('login')) aria-current="page" @endif class="flex flex-col items-center gap-0.5 {{ request()->routeIs('customer.*') || request()->routeIs('login') ? 'text-brand-500' : 'text-slate-400' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ request()->routeIs('customer.*') ? 'bg-brand-500 text-white shadow-md shadow-brand-500/30' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </a>
    </nav>

    <!-- Footer (Image 2 style Minimalist footer) -->
    <footer class="site-footer pt-12 pb-8">
        <div class="ui-container">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-12">
                <div class="footer-brand col-span-2 min-w-0">
                    <a href="{{ url('/') }}" class="inline-flex min-w-0 items-center gap-2">
                        @if ($siteSettings->logo_url)
                            <img src="{{ $siteSettings->logo_url }}" alt="" class="h-10 w-auto max-w-24 object-contain">
                        @endif
                        <span class="footer-brand break-words text-2xl font-black tracking-tight">{{ $siteSettings->site_name }}</span>
                    </a>
                    @if ($siteSettings->footer_description)
                        <p class="mt-3 max-w-sm text-xs leading-relaxed footer-link">{{ $siteSettings->footer_description }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap items-center gap-3 text-slate-400">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500" aria-hidden="true"></span> {{ __('100% Authentic Products') }}
                        </span>
                    </div>
                    @if ($siteSettings->contact_email || $siteSettings->contact_phone)
                        <div class="mt-4 space-y-2 text-xs">
                            @if ($siteSettings->contact_email)
                                <a href="mailto:{{ $siteSettings->contact_email }}" class="footer-link block break-all">{{ $siteSettings->contact_email }}</a>
                            @endif
                            @if ($siteSettings->contact_phone)
                                <a href="tel:{{ $siteSettings->contact_phone }}" class="footer-link block break-all">{{ $siteSettings->contact_phone }}</a>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="footer-column">
                    <h4 class="footer-heading text-xs font-bold uppercase tracking-wider mb-3">{{ __('Shop') }}</h4>
                    <ul class="footer-links space-y-2 text-xs font-medium">
                        <li><a href="{{ route('products.index') }}" class="footer-link">{{ __('All Products') }}</a></li>
                        <li><a href="{{ route('products.index') }}" class="footer-link">{{ __('Electronics & Tech') }}</a></li>
                        <li><a href="{{ route('products.index') }}" class="footer-link">{{ __('Living & Gadgets') }}</a></li>
                        <li><a href="{{ route('products.index') }}" class="footer-link">{{ __('Special Deals') }}</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4 class="footer-heading text-xs font-bold uppercase tracking-wider mb-3">{{ __('Vendors') }}</h4>
                    <ul class="footer-links space-y-2 text-xs font-medium">
                        <li><a href="{{ route('vendor.register.create') }}" class="footer-link">{{ __('Become a Seller') }}</a></li>
                        <li><a href="{{ route('vendor.dashboard') }}" class="footer-link">{{ __('Vendor Portal') }}</a></li>
                        <li><a href="{{ route('vendor.registration.status') }}" class="footer-link">{{ __('Application Status') }}</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4 class="footer-heading text-xs font-bold uppercase tracking-wider mb-3">{{ __('Customer') }}</h4>
                    <ul class="footer-links space-y-2 text-xs font-medium">
                        <li><a href="{{ route('customer.dashboard') }}" class="footer-link">{{ __('My Account') }}</a></li>
                        <li><a href="{{ route('orders.index') }}" class="footer-link">{{ __('Order Tracking') }}</a></li>
                        <li><a href="{{ route('cart.index') }}" class="footer-link">{{ __('Shopping Cart') }}</a></li>
                        <li><a href="{{ route('wishlist.index') }}" class="footer-link">{{ __('Wishlist') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-divider pt-8 border-t flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <div>&copy; {{ date('Y') }} {{ $siteSettings->copyright_text }}</div>
                @php
                    $publishedPages = \App\Modules\Cms\Models\CmsPage::where('is_published', true)->orderBy('title')->get();
                @endphp
                <div class="flex flex-wrap items-center gap-4">
                    @forelse ($publishedPages as $page)
                        <a href="{{ route('cms.show', $page->slug) }}" class="hover:text-slate-700">{{ $page->title }}</a>
                    @empty
                        <span>{{ __('No published pages') }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
