<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Dashboard')) - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#F4F6F9] text-[#1e293b] antialiased" x-data="{ sidebarOpen: true, mobileMenu: false }">
    <div class="flex min-h-screen">
        <!-- Sidebar Backdrop for mobile -->
        <div x-show="mobileMenu" @click="mobileMenu = false" class="fixed inset-0 z-40 bg-black/50 md:hidden" x-cloak></div>

        <!-- Sidebar -->
        <aside :class="mobileMenu ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-[#181F2C] text-slate-300 flex flex-col transition-transform duration-200 ease-in-out border-r border-slate-800">
            <!-- Brand -->
            <div class="h-16 px-6 flex items-center justify-between border-b border-slate-800/80">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#6852D9] to-[#8c7ae6] flex items-center justify-center text-white shadow-md shadow-[#6852D9]/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-extrabold text-white text-lg tracking-tight">NEXTON</span>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block font-semibold leading-none">Commerce OS</span>
                    </div>
                </a>
                <button @click="mobileMenu = false" class="md:hidden text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto text-xs font-semibold">
                <div>
                    <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">Navigation</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#6852D9] text-white font-medium shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>{{ __('Dashboard') }}</span>
                            </div>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 text-white' : 'bg-emerald-500/20 text-emerald-400' }}">Live</span>
                        </a>
                    </div>
                </div>

                <div>
                    <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">E-Commerce</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.products.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>{{ __('Products') }}</span>
                        </a>
                        <a href="{{ route('admin.inventory.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.inventory.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>{{ __('Inventory') }}</span>
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.orders.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span>{{ __('Orders') }}</span>
                        </a>
                        <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.payments.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>{{ __('Payments') }}</span>
                        </a>
                        <a href="{{ route('admin.shipping.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.shipping.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            <span>{{ __('Shipping') }}</span>
                        </a>
                        <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.coupons.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span>{{ __('Coupons') }}</span>
                        </a>
                    </div>
                </div>

                <div>
                    <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">Marketplace & Partners</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.customers.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>{{ __('Customers') }}</span>
                        </a>
                        <a href="{{ route('admin.vendors.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.vendors.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ __('Vendors') }}</span>
                        </a>
                        <a href="{{ route('admin.commissions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.commissions.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ __('Commissions') }}</span>
                        </a>
                        <a href="{{ route('admin.settlements.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.settlements.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span>{{ __('Settlements') }}</span>
                        </a>
                        <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.subscriptions.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            <span>{{ __('Subscriptions') }}</span>
                        </a>
                    </div>
                </div>

                <div>
                    <div class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">Content & System</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.reviews.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <span>{{ __('Reviews') }}</span>
                        </a>
                        <a href="{{ route('admin.cms.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.cms.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            <span>{{ __('CMS Pages') }}</span>
                        </a>
                        <a href="{{ route('admin.notifications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.notifications.*') ? 'bg-slate-800 text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span>{{ __('Notifications') }}</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- User Footer in Sidebar -->
            <div class="p-3 border-t border-slate-800/80 flex items-center justify-between bg-slate-900/50">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center font-bold text-slate-200 shrink-0 text-xs">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="truncate text-xs">
                        <div class="font-medium text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</div>
                        <div class="text-[10px] text-slate-400 truncate">Administrator</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Column -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="sticky top-0 z-30 h-16 bg-white border-b border-[#E8EBF0] px-4 md:px-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-slate-800 leading-tight">@yield('title', __('Dashboard'))</h1>
                        <div class="text-[11px] text-slate-400 hidden sm:block">Nexton OS &bull; Control Panel</div>
                    </div>
                </div>

                <!-- Right controls -->
                <div class="flex items-center gap-3">
                    <!-- Search input -->
                    <div class="relative hidden sm:block w-64">
                        <input type="text" placeholder="{{ __('Quick search...') }}" class="w-full pl-9 pr-4 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6852D9]/30 focus:border-[#6852D9] transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <!-- Store link -->
                    <a href="{{ url('/') }}" target="_blank" class="text-xs font-semibold px-3 py-1.5 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span class="hidden md:inline">{{ __('View Store') }}</span>
                    </a>

                    <!-- User menu -->
                    <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#6852D9] to-indigo-400 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <span class="text-xs font-semibold text-slate-700 hidden lg:inline">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 md:p-8">
                @if (session('status'))
                    <div role="status" class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs font-medium text-emerald-800 flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
