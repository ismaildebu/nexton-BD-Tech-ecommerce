<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Account')) · {{ $siteSettings->site_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <a href="#account-main" class="sr-only z-[100] rounded-lg bg-white px-4 py-3 font-semibold focus:not-sr-only focus:fixed focus:left-4 focus:top-4">{{ __('Skip to content') }}</a>
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8" aria-label="{{ __('Account navigation') }}">
            <x-ui.brand compact />
            <div class="hidden items-center gap-1 md:flex">
                <a href="{{ route('products.index') }}" class="ui-btn ui-btn-ghost ui-btn-sm">{{ __('Store') }}</a>
                @auth
                    <a href="{{ route('customer.dashboard') }}" class="ui-btn ui-btn-ghost ui-btn-sm">{{ __('Dashboard') }}</a>
                    <a href="{{ route('profile.edit') }}" class="ui-btn ui-btn-ghost ui-btn-sm {{ request()->routeIs('profile.*') ? 'bg-brand-50 text-brand-700' : '' }}">{{ __('Profile') }}</a>
                    <a href="{{ route('addresses.index') }}" class="ui-btn ui-btn-ghost ui-btn-sm {{ request()->routeIs('addresses.*') ? 'bg-brand-50 text-brand-700' : '' }}">{{ __('Addresses') }}</a>
                    <a href="{{ route('vendor.dashboard') }}" class="ui-btn ui-btn-outline ui-btn-sm">{{ __('Vendor portal') }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="ui-btn ui-btn-ghost ui-btn-sm text-rose-600">{{ __('Sign out') }}</button></form>
                @else
                    <a href="{{ route('login') }}" class="ui-btn ui-btn-outline ui-btn-sm">{{ __('Sign in') }}</a>
                @endauth
            </div>
            <details class="relative md:hidden">
                <summary class="ui-icon-btn list-none cursor-pointer" aria-label="{{ __('Open account menu') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </summary>
                <div class="absolute right-0 top-12 z-50 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                    <a href="{{ route('products.index') }}" class="ui-sidebar-link text-slate-600 hover:bg-slate-50">{{ __('Store') }}</a>
                    @auth
                        <a href="{{ route('customer.dashboard') }}" class="ui-sidebar-link text-slate-600 hover:bg-slate-50">{{ __('Dashboard') }}</a>
                        <a href="{{ route('profile.edit') }}" class="ui-sidebar-link text-slate-600 hover:bg-slate-50">{{ __('Profile') }}</a>
                        <a href="{{ route('addresses.index') }}" class="ui-sidebar-link text-slate-600 hover:bg-slate-50">{{ __('Addresses') }}</a>
                        <a href="{{ route('vendor.dashboard') }}" class="ui-sidebar-link text-slate-600 hover:bg-slate-50">{{ __('Vendor portal') }}</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="ui-sidebar-link w-full text-rose-600 hover:bg-rose-50">{{ __('Sign out') }}</button></form>
                    @else
                        <a href="{{ route('login') }}" class="ui-sidebar-link text-slate-600 hover:bg-slate-50">{{ __('Sign in') }}</a>
                    @endauth
                </div>
            </details>
        </nav>
    </header>

    <main id="account-main" class="ui-page-narrow flex-1">
        @include('layouts.partials.flash')
        @yield('content')
    </main>
</body>
</html>
