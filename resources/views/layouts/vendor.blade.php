<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-gray-900 antialiased">
    @php
        $vendor = $vendor ?? auth()->user()?->vendor;
        $link = fn (bool $active) => 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition '
            . ($active ? 'bg-teal-50 text-teal-800' : 'text-gray-600 hover:bg-stone-100 hover:text-gray-900');
    @endphp

    <div class="flex min-h-screen">
        {{-- Desktop sidebar --}}
        <aside class="hidden w-64 shrink-0 flex-col border-r border-stone-200 bg-white md:flex">
            <div class="flex h-16 items-center border-b border-stone-200 px-5">
                <a href="{{ route('vendor.dashboard') }}" class="text-lg font-bold text-teal-800">
                    {{ config('app.name') }}
                </a>
            </div>

            <nav class="flex-1 space-y-1 p-3">
                <a href="{{ route('vendor.dashboard') }}" class="{{ $link(request()->routeIs('vendor.dashboard')) }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10"/></svg>
                    {{ __('Dashboard') }}
                </a>
                <a href="{{ route('vendor.products.index') }}" class="{{ $link(request()->routeIs('vendor.products.*')) }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4"/></svg>
                    {{ __('Products') }}
                </a>

                @if ($vendor)
                    <a href="{{ route('vendor.documents.index', $vendor) }}" class="{{ $link(request()->routeIs('vendor.documents.*')) }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3h7l5 5v13H7V3z"/></svg>
                        {{ __('Documents') }}
                    </a>
                    <a href="{{ route('vendor.staff.index', $vendor) }}" class="{{ $link(request()->routeIs('vendor.staff.*')) }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2m18 0v-2a4 4 0 00-3-3.87M14 4a4 4 0 010 7.75M10 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                        {{ __('Staff') }}
                    </a>
                    <a href="{{ route('vendor.registration.status') }}" class="{{ $link(request()->routeIs('vendor.registration.status')) }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('Registration status') }}
                    </a>
                @else
                    <a href="{{ route('vendor.register.create') }}" class="{{ $link(request()->routeIs('vendor.register.*')) }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Register as vendor') }}
                    </a>
                @endif
            </nav>

            <div class="border-t border-stone-200 p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('Sign out') }}
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Top bar --}}
            <header class="flex h-16 items-center justify-between border-b border-stone-200 bg-white px-4 md:px-6">
                <div class="md:hidden">
                    <details class="relative">
                        <summary class="cursor-pointer list-none rounded-lg p-2 text-gray-700 hover:bg-stone-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </summary>
                        <nav class="absolute left-0 top-12 z-20 w-56 space-y-1 rounded-lg border border-stone-200 bg-white p-2 shadow-lg">
                            <a href="{{ route('vendor.dashboard') }}" class="{{ $link(request()->routeIs('vendor.dashboard')) }}">{{ __('Dashboard') }}</a>
                            <a href="{{ route('vendor.products.index') }}" class="{{ $link(request()->routeIs('vendor.products.*')) }}">{{ __('Products') }}</a>
                            @if ($vendor)
                                <a href="{{ route('vendor.documents.index', $vendor) }}" class="{{ $link(request()->routeIs('vendor.documents.*')) }}">{{ __('Documents') }}</a>
                                <a href="{{ route('vendor.staff.index', $vendor) }}" class="{{ $link(request()->routeIs('vendor.staff.*')) }}">{{ __('Staff') }}</a>
                                <a href="{{ route('vendor.registration.status') }}" class="{{ $link(request()->routeIs('vendor.registration.status')) }}">{{ __('Registration status') }}</a>
                            @else
                                <a href="{{ route('vendor.register.create') }}" class="{{ $link(request()->routeIs('vendor.register.*')) }}">{{ __('Register as vendor') }}</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-700">{{ __('Sign out') }}</button>
                            </form>
                        </nav>
                    </details>
                </div>

                <p class="hidden text-sm text-gray-500 md:block">{{ __('Vendor panel') }}</p>

                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-700 text-sm font-semibold text-white">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                </div>
            </header>

            <main class="flex-1 p-4 md:p-8">
                @if (session('status'))
                    <div role="status" class="mb-6 rounded-lg border border-teal-200 bg-teal-50 p-3 text-sm text-teal-900">{{ session('status') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>