@extends('layouts.app')

@section('title', __('Account overview'))

@section('content')
    <x-ui.page-header :title="__('Your account')" :description="__('Your details, verification status and shopping shortcuts in one place.')"
        :eyebrow="__('Customer account')">
        <x-slot:action>
            <x-ui.button :href="route('profile.edit')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.86 3.5a2.12 2.12 0 013 3L8 18.36l-4 1 1-4L16.86 3.5z"/></svg>
                {{ __('Edit profile') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    <nav class="ui-card mb-7 overflow-hidden p-2" aria-label="{{ __('Account sections') }}">
        <div class="ui-scrollbar flex gap-1 overflow-x-auto">
            @foreach ([
                ['customer.dashboard', __('Dashboard')],
                ['customer.account.index', __('Overview')],
                ['profile.edit', __('Profile')],
                ['orders.index', __('Orders')],
                ['addresses.index', __('Addresses')],
                ['payments.index', __('Payments')],
                ['shipping.index', __('Shipping')],
                ['reviews.index', __('Reviews')],
                ['wishlist.index', __('Wishlist')],
            ] as [$routeName, $label])
                <a href="{{ route($routeName) }}" @if(request()->routeIs($routeName)) aria-current="page" @endif
                    class="shrink-0 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 {{ request()->routeIs($routeName) ? 'bg-ink-900 text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">{{ $label }}</a>
            @endforeach
        </div>
    </nav>

    <section class="relative mb-6 overflow-hidden rounded-2xl bg-ink-900 p-6 text-white shadow-sm sm:p-8">
        <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -bottom-24 left-1/3 h-52 w-52 rounded-full bg-accent-500/20 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-xl font-extrabold ring-1 ring-white/15">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-400">{{ __('Account holder') }}</p>
                <h2 class="mt-1 truncate text-2xl font-extrabold">{{ $user->name }}</h2>
                <p class="mt-1 break-all text-sm text-slate-300">{{ $user->email ?? $user->phone }}</p>
            </div>
            <x-ui.badge :tone="$user->status->value === 'active' ? 'success' : ($user->status->value === 'suspended' ? 'danger' : 'warning')">
                {{ $user->status->label() }}
            </x-ui.badge>
        </div>
    </section>

    <div class="grid items-start gap-6 lg:grid-cols-[1.15fr_0.85fr]">
        <x-ui.card>
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 class="ui-section-title">{{ __('Personal details') }}</h2>
                <a href="{{ route('profile.edit') }}" class="ui-link text-sm">{{ __('Manage') }}</a>
            </div>
            <dl class="divide-y divide-slate-100">
                <div class="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-4">
                    <dt class="text-sm font-semibold text-slate-500">{{ __('Full name') }}</dt>
                    <dd class="text-sm font-semibold text-slate-900">{{ $user->name }}</dd>
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[10rem_1fr] sm:items-center sm:gap-4">
                    <dt class="text-sm font-semibold text-slate-500">{{ __('Email') }}</dt>
                    <dd class="flex flex-wrap items-center gap-2 text-sm text-slate-800">
                        <span class="break-all">{{ $user->email ?? '—' }}</span>
                        @if ($user->email !== null)
                            <x-ui.badge :tone="$user->hasVerifiedEmail() ? 'success' : 'warning'">{{ $user->hasVerifiedEmail() ? __('Verified') : __('Not verified') }}</x-ui.badge>
                        @endif
                    </dd>
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[10rem_1fr] sm:items-center sm:gap-4">
                    <dt class="text-sm font-semibold text-slate-500">{{ __('Mobile number') }}</dt>
                    <dd class="flex flex-wrap items-center gap-2 text-sm text-slate-800">
                        <span>{{ $user->phone ?? '—' }}</span>
                        @if ($user->phone !== null)
                            <x-ui.badge :tone="$user->hasVerifiedPhone() ? 'success' : 'warning'">{{ $user->hasVerifiedPhone() ? __('Verified') : __('Not verified') }}</x-ui.badge>
                        @endif
                    </dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-4">
                    <dt class="text-sm font-semibold text-slate-500">{{ __('Preferred language') }}</dt>
                    <dd class="text-sm text-slate-800">{{ $user->customerProfile?->preferred_language === 'bn' ? __('Bangla') : ($user->customerProfile?->preferred_language === 'en' ? __('English') : __('Not set')) }}</dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[10rem_1fr] sm:gap-4">
                    <dt class="text-sm font-semibold text-slate-500">{{ __('Date of birth') }}</dt>
                    <dd class="text-sm text-slate-800">{{ $user->customerProfile?->date_of_birth?->format('d M Y') ?? '—' }}</dd>
                </div>
            </dl>

            @if (($user->email !== null && ! $user->hasVerifiedEmail()) || ($user->phone !== null && ! $user->hasVerifiedPhone()))
                <x-ui.alert tone="warning" class="mt-5">
                    {{ __('Verify your contact details to secure your account.') }}
                    <a href="{{ route('verification.notice') }}" class="ml-1 font-bold underline underline-offset-2">{{ __('Verify now') }}</a>
                </x-ui.alert>
            @endif
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card>
                <h2 class="ui-section-title">{{ __('Quick links') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Pick up where you left off.') }}</p>
                <div class="mt-5 grid gap-3">
                    @foreach ([
                        ['orders.index', __('View your orders'), __('Track orders and view details'), 'M3 3h18v18H3V3zm0 6h18M9 21V9'],
                        ['addresses.index', __('Manage addresses'), __('Update delivery information'), 'M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12zM14.5 9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                        ['wishlist.index', __('Open your wishlist'), __('Keep an eye on your favorites'), 'M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 00-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 000-7.8z'],
                    ] as [$routeName, $title, $description, $path])
                        <a href="{{ route($routeName) }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-brand-200 hover:bg-brand-50/40 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-600 group-hover:bg-white group-hover:text-brand-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-slate-900">{{ $title }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $description }}</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card class="bg-slate-50/70">
                <h2 class="text-sm font-bold text-slate-900">{{ __('Need help?') }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Visit your dashboard for recent activity, or head back to the store to continue shopping.') }}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-ui.button :href="route('customer.dashboard')" variant="outline" size="sm">{{ __('Dashboard') }}</x-ui.button>
                    <x-ui.button :href="route('products.index')" variant="ghost" size="sm">{{ __('Continue shopping') }}</x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection
