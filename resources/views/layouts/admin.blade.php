<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="min-h-screen bg-[#F4F6F9] text-[#343A46] antialiased">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-[#253442] text-white flex flex-col">
            <div class="px-4 py-4 border-b border-white/10">
                <div class="font-bold text-lg">{{ config('app.name') }}</div>
                <div class="text-xs text-white/60">Admin</div>
            </div>
            <nav class="flex-1 p-3 space-y-1 text-sm overflow-y-auto">
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10' : '' }}">{{ __('Dashboard') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.customers.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Customers') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.products.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Products') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.orders.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Orders') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.payments.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Payments') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.shipping.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Shipping') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.commissions.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Commissions') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.settlements.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Settlements') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.subscriptions.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Subscriptions') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.vendors.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Vendors') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.inventory.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Inventory') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.reviews.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Reviews') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.cms.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('CMS') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.notifications.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Notifications') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.wishlist.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Wishlist') }}</a>
                <a onmouseenter="window.location.href=this.href" href="{{ route('admin.coupons.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">{{ __('Coupons') }}</a>
            </nav>
        </aside>
        <div class="flex-1 flex flex-col">
            <header class="sticky top-0 z-10 bg-white border-b border-[#E8EBF0] px-4 py-3 flex items-center justify-between">
                <div class="font-semibold text-[15px]">@yield('title', __('Dashboard'))</div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-[#8993A4]">{{ auth()->user()->name ?? '' }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="text-gray-600 hover:text-red-700">{{ __('Sign out') }}</button></form>
                </div>
            </header>
            <main class="p-6">
                @if (session('status'))
                    <div role="status" class="mb-6 rounded-md bg-teal-50 p-3 text-sm text-teal-900">{{ session('status') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
