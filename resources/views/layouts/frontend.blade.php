<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'NEXTON Store'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#F8F9FA] text-[#1E293B] antialiased flex flex-col justify-between" x-data="{ mobileSearch: false, mobileDrawer: false }">
    <!-- Top Global Notification or Announcement Bar -->
    <div class="bg-[#181F2C] text-white text-[11px] py-2 px-4 text-center font-medium">
        <span>✨ Free Nationwide Express Delivery on orders over ৳ 2,500!</span>
        <a href="{{ route('products.index') }}" class="underline ml-2 text-orange-400 hover:text-orange-300">Shop Now</a>
    </div>

    <!-- Main Navigation Header (Image 2 ESTORE Style) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-100 transition shadow-xs">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between gap-4">
                <!-- Left Nav Links (Desktop) -->
                <nav class="hidden lg:flex items-center gap-7 text-xs font-semibold text-slate-600">
                    <a href="{{ route('products.index') }}" class="hover:text-[#FF5B22] transition {{ request()->routeIs('products.*') ? 'text-[#FF5B22]' : '' }}">{{ __('Catalog') }}</a>
                    <a href="{{ url('/') }}#popular" class="hover:text-[#FF5B22] transition">{{ __('Popular') }}</a>
                    <a href="{{ url('/') }}#categories" class="hover:text-[#FF5B22] transition">{{ __('Categories') }}</a>
                    <a href="{{ url('/') }}#vendors" class="hover:text-[#FF5B22] transition">{{ __('Vendors') }}</a>
                    <a href="{{ route('customer.dashboard') }}" class="hover:text-[#FF5B22] transition">{{ __('Support') }}</a>
                </nav>

                <!-- Mobile Hamburger Button -->
                <button @click="mobileDrawer = !mobileDrawer" class="lg:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-xl transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Center Logo (Bold Minimalist ESTORE style) -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <span class="text-2xl font-black tracking-tighter text-[#181F2C]">NEXT<span class="text-[#FF5B22]">ON</span></span>
                    </a>
                </div>

                <!-- Right Action Icons (Image 2: Search, Wishlist, Cart, Profile) -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <!-- Search Button / Bar -->
                    <a href="{{ route('products.index') }}" class="p-2 text-slate-600 hover:text-[#FF5B22] hover:bg-slate-100 rounded-full transition" title="Search">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </a>

                    <!-- Wishlist Icon -->
                    <a href="{{ route('wishlist.index') }}" class="p-2 text-slate-600 hover:text-[#FF5B22] hover:bg-slate-100 rounded-full transition relative" title="Wishlist">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </a>

                    <!-- Cart Icon with badge -->
                    <a href="{{ route('cart.index') }}" class="p-2 text-slate-600 hover:text-[#FF5B22] hover:bg-slate-100 rounded-full transition relative" title="Shopping Cart">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </a>

                    <!-- Profile / Auth -->
                    @auth
                        <div class="relative flex items-center gap-2 pl-2 border-l border-slate-200">
                            <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('customer.dashboard') }}" class="flex items-center gap-2 text-xs font-semibold text-slate-700 hover:text-[#FF5B22]">
                                <div class="w-8 h-8 rounded-full bg-[#181F2C] text-white flex items-center justify-center font-bold text-xs">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="hidden md:inline">{{ auth()->user()->name }}</span>
                            </a>
                        </div>
                    @else
                        <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                            <a href="{{ route('login') }}" class="text-xs font-semibold px-3 py-1.5 text-slate-700 hover:text-[#FF5B22] transition">
                                {{ __('Sign In') }}
                            </a>
                            <a href="{{ route('register') }}" class="hidden sm:inline-flex text-xs font-bold px-4 py-2 rounded-full bg-[#181F2C] text-white hover:bg-black transition shadow-sm">
                                {{ __('Join') }}
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileDrawer" @click="mobileDrawer = false" class="fixed inset-0 z-50 bg-black/50 lg:hidden" x-cloak></div>
        <div x-show="mobileDrawer" class="fixed inset-y-0 left-0 z-50 w-72 bg-white p-6 shadow-2xl lg:hidden flex flex-col justify-between" x-cloak>
            <div>
                <div class="flex items-center justify-between mb-8">
                    <span class="text-2xl font-black tracking-tight text-[#181F2C]">NEXT<span class="text-[#FF5B22]">ON</span></span>
                    <button @click="mobileDrawer = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-4 text-sm font-semibold text-slate-700">
                    <a href="{{ url('/') }}" class="block py-2 hover:text-[#FF5B22]">Home</a>
                    <a href="{{ route('products.index') }}" class="block py-2 hover:text-[#FF5B22]">Catalog & Products</a>
                    <a href="{{ route('cart.index') }}" class="block py-2 hover:text-[#FF5B22]">Cart</a>
                    <a href="{{ route('wishlist.index') }}" class="block py-2 hover:text-[#FF5B22]">Wishlist</a>
                    @auth
                        <a href="{{ route('customer.dashboard') }}" class="block py-2 hover:text-[#FF5B22]">My Dashboard</a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="block py-2 text-[#6852D9] font-bold">Admin Panel</a>
                        @endif
                    @endauth
                </div>
            </div>
            <div class="pt-6 border-t border-slate-100">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-center py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200">
                            {{ __('Sign out') }}
                        </button>
                    </form>
                @else
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="text-center py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold">{{ __('Login') }}</a>
                        <a href="{{ route('register') }}" class="text-center py-2.5 rounded-xl bg-[#FF5B22] text-white text-xs font-bold">{{ __('Register') }}</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 pb-24 md:pb-16">
        @if (session('status'))
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4">
                <div role="status" class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-xs font-semibold text-emerald-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Floating Mobile Bottom Navigation Bar (Image 4 Style) -->
    <nav class="md:hidden fixed bottom-4 inset-x-4 z-40 bg-white/90 backdrop-blur-lg border border-slate-200/80 rounded-full py-2 px-6 shadow-xl flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex flex-col items-center gap-0.5 {{ request()->is('/') ? 'text-[#FF5B22]' : 'text-slate-400' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ request()->is('/') ? 'bg-[#FF5B22] text-white shadow-md shadow-[#FF5B22]/30' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('wishlist.index') }}" class="flex flex-col items-center gap-0.5 {{ request()->routeIs('wishlist.*') ? 'text-[#FF5B22]' : 'text-slate-400' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ request()->routeIs('wishlist.*') ? 'bg-[#FF5B22] text-white shadow-md shadow-[#FF5B22]/30' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('cart.index') }}" class="flex flex-col items-center gap-0.5 {{ request()->routeIs('cart.*') ? 'text-[#FF5B22]' : 'text-slate-400' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ request()->routeIs('cart.*') ? 'bg-[#FF5B22] text-white shadow-md shadow-[#FF5B22]/30' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
        </a>

        <a href="{{ auth()->check() ? (auth()->user()->is_admin ? route('admin.dashboard') : route('customer.dashboard')) : route('login') }}" class="flex flex-col items-center gap-0.5 {{ request()->routeIs('customer.*') || request()->routeIs('login') ? 'text-[#FF5B22]' : 'text-slate-400' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ request()->routeIs('customer.*') ? 'bg-[#FF5B22] text-white shadow-md shadow-[#FF5B22]/30' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </a>
    </nav>

    <!-- Footer (Image 2 style Minimalist footer) -->
    <footer class="bg-white border-t border-slate-100 pt-12 pb-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-12">
                <div class="col-span-2">
                    <span class="text-2xl font-black tracking-tight text-[#181F2C]">NEXT<span class="text-[#FF5B22]">ON</span></span>
                    <p class="mt-3 text-xs text-slate-500 max-w-sm leading-relaxed">
                        The premium multivendor commerce platform in Bangladesh. Discover verified merchants, curated electronics, gadgets, and design essentials.
                    </p>
                    <div class="flex items-center gap-3 mt-4 text-slate-400">
                        <span class="text-xs font-semibold text-emerald-600 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> 100% Authentic Products
                        </span>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Shop</h4>
                    <ul class="space-y-2 text-xs text-slate-500 font-medium">
                        <li><a href="{{ route('products.index') }}" class="hover:text-slate-900">All Products</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-slate-900">Electronics & Tech</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-slate-900">Living & Gadgets</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-slate-900">Special Deals</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Vendors</h4>
                    <ul class="space-y-2 text-xs text-slate-500 font-medium">
                        <li><a href="{{ route('vendor.register.create') }}" class="hover:text-slate-900">Become a Seller</a></li>
                        <li><a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-900">Vendor Portal</a></li>
                        <li><a href="{{ route('vendor.registration.status') }}" class="hover:text-slate-900">Application Status</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Customer</h4>
                    <ul class="space-y-2 text-xs text-slate-500 font-medium">
                        <li><a href="{{ route('customer.dashboard') }}" class="hover:text-slate-900">My Account</a></li>
                        <li><a href="{{ route('orders.index') }}" class="hover:text-slate-900">Order Tracking</a></li>
                        <li><a href="{{ route('cart.index') }}" class="hover:text-slate-900">Shopping Cart</a></li>
                        <li><a href="{{ route('wishlist.index') }}" class="hover:text-slate-900">Wishlist</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <div>&copy; {{ date('Y') }} Nexton BD Tech E-Commerce Ltd. All rights reserved.</div>
                <div class="flex items-center gap-4">
                    <span>Privacy Policy</span>
                    <span>Terms of Service</span>
                    <span>Security</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
