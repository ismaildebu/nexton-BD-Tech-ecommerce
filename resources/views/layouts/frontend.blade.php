<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-gray-900 antialiased">
    <header class="border-b border-[#E8EBF0] bg-white sticky top-0 z-10">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ url('/') }}" class="font-bold text-[#253442] text-lg">{{ config('app.name') }}</a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('products.index') }}" class="hover:text-[#6852D9]">{{ __('Products') }}</a>
                <a href="{{ route('login') }}" class="hover:text-[#6852D9]">{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="bg-[#6852D9] text-white px-3 py-1 rounded">{{ __('Register') }}</a>
            </div>
        </nav>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div role="status" class="mb-6 rounded-md bg-teal-50 p-3 text-sm text-teal-900">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
    <footer class="border-t border-stone-200 bg-white mt-12">
        <div class="mx-auto max-w-6xl px-4 py-6 text-sm text-gray-600">
            © {{ date('Y') }} {{ config('app.name') }}
        </div>
    </footer>
</body>
</html>
