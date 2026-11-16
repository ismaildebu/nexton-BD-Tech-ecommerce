<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-gray-900 antialiased">
    <div class="flex min-h-screen">
        <aside class="w-64 border-r border-stone-200 bg-white p-4 hidden md:block">
            <nav class="space-y-2 text-sm">
                <a href="{{ route('vendor.dashboard') }}" class="block hover:text-teal-800">{{ __('Dashboard') }}</a>
                <a href="{{ route('products.index') }}" class="block hover:text-teal-800">{{ __('Products') }}</a>
                <a href="{{ route('vendor.register.create') }}" class="block hover:text-teal-800">{{ __('Register') }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="text-gray-600 hover:text-red-700">{{ __('Sign out') }}</button></form>
            </nav>
        </aside>
        <main class="flex-1 p-6">
            @if (session('status'))
                <div role="status" class="mb-6 rounded-md bg-teal-50 p-3 text-sm text-teal-900">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
