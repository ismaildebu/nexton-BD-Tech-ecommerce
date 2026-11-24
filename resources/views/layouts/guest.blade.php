<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-white text-slate-900 lg:grid lg:grid-cols-[1.05fr_1fr]">
    <section class="relative hidden overflow-hidden bg-ink-900 p-12 text-white lg:flex lg:flex-col lg:justify-between xl:p-16">
        <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -bottom-40 -left-24 h-[28rem] w-[28rem] rounded-full bg-accent-600/25 blur-3xl" aria-hidden="true"></div>
        <div class="relative"><x-ui.brand inverse /></div>
        <div class="relative max-w-lg py-16">
            <p class="ui-eyebrow text-brand-400">{{ __('Verified marketplace') }}</p>
            <h2 class="text-4xl font-extrabold leading-tight xl:text-5xl">{{ __('A better way to shop, sell and grow.') }}</h2>
            <p class="mt-6 text-base leading-8 text-slate-300">{{ __('Discover trusted merchants, manage every order and keep your business moving with one secure platform.') }}</p>
            <div class="mt-10 grid gap-4 sm:grid-cols-3">
                @foreach ([__('Secure payments'), __('Verified vendors'), __('Order tracking')] as $feature)
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm font-semibold text-slate-200 backdrop-blur">{{ $feature }}</div>
                @endforeach
            </div>
        </div>
        <p class="relative text-xs text-slate-500">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
    </section>

    <main class="flex min-h-screen flex-col px-4 py-6 sm:px-8 lg:px-12 xl:px-20">
        <div class="lg:hidden"><x-ui.brand /></div>
        <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10">
            <div class="mb-8">
                <p class="ui-eyebrow">{{ config('app.name') }}</p>
                <h1 class="text-3xl font-extrabold text-slate-950">@yield('heading')</h1>
            </div>

            @include('layouts.partials.flash')

            <div class="ui-card p-6 sm:p-8">
                @yield('content')
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-slate-500 transition hover:text-brand-600">{{ __('Back to store') }}</a>
            </div>
        </div>
    </main>
</body>
</html>
