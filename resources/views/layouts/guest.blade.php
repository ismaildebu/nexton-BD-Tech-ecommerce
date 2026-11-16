<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-gray-900 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <a href="{{ url('/') }}" class="mb-6 block text-center text-2xl font-semibold text-teal-800">{{ config('app.name') }}</a>

        <div class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            <h1 class="mb-5 text-xl font-semibold">@yield('heading')</h1>

            @if (session('status'))
                <div role="status" class="mb-4 rounded-md bg-teal-50 p-3 text-sm text-teal-900">{{ session('status') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
