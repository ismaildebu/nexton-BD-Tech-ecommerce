<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-gray-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <nav class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <a href="{{ url('/') }}" class="font-semibold text-teal-800">{{ config('app.name') }}</a>
            <div class="flex items-center gap-5 text-sm">
                <a href="{{ route('profile.edit') }}" class="hover:text-teal-800">{{ __('Profile') }}</a>
                <a href="{{ route('addresses.index') }}" class="hover:text-teal-800">{{ __('Addresses') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:text-red-700">{{ __('Sign out') }}</button>
                </form>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-8">
        @if (session('status'))
            <div role="status" class="mb-6 rounded-md bg-teal-50 p-3 text-sm text-teal-900">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
