@extends('layouts.app')
@section('title', __('Customer dashboard'))

@section('content')
    <div class="ui-page ui-page-narrow">
        <x-ui.page-header
            :title="__('Welcome back, :name', ['name' => $user->name])"
            :description="__('Manage your profile, addresses and account details from one place.')"
            :eyebrow="__('Customer dashboard')"
        >
            <x-slot:action>
                <x-ui.button :href="route('customer.account.index')" variant="outline">
                    {{ __('View account') }}
                </x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
            <x-ui.card class="p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-xl font-bold text-brand-700">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                        <p class="mt-1 break-words text-sm text-slate-500">{{ $user->email ?? $user->phone }}</p>
                        <span class="mt-4 inline-flex">
                            <x-ui.badge tone="accent">{{ __('Customer account') }}</x-ui.badge>
                        </span>
                    </div>
                </div>

                <dl class="mt-8 grid gap-4 rounded-2xl bg-slate-50 p-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Saved addresses') }}</dt>
                        <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $addressCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Default address') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">
                            {{ $defaultAddress ? $defaultAddress->full_name : __('Not set') }}
                        </dd>
                    </div>
                </dl>

                @if ($defaultAddress)
                    <div class="mt-5 flex gap-3 rounded-xl border border-slate-200 p-4">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/></svg>
                        <p class="text-sm leading-6 text-slate-600">{{ $defaultAddress->address_line }}</p>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card class="p-6 sm:p-8">
                <h2 class="text-lg font-bold text-slate-900">{{ __('Quick links') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Continue where you left off.') }}</p>
                <nav class="mt-6 space-y-3" aria-label="{{ __('Account shortcuts') }}">
                    <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-brand-200 hover:bg-brand-50/50">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition group-hover:bg-white group-hover:text-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 1116 0"/></svg>
                        </span>
                        <span class="flex-1 text-sm font-semibold text-slate-800">{{ __('Profile') }}</span>
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('addresses.index') }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-brand-200 hover:bg-brand-50/50">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition group-hover:bg-white group-hover:text-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="2.5"/></svg>
                        </span>
                        <span class="flex-1 text-sm font-semibold text-slate-800">{{ __('Addresses') }}</span>
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('customer.account.index') }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-brand-200 hover:bg-brand-50/50">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition group-hover:bg-white group-hover:text-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 6a2 2 0 012-2h12a2 2 0 012 2M4 6v12a2 2 0 002 2h12a2 2 0 002-2V6M8 10h8M8 14h5"/></svg>
                        </span>
                        <span class="flex-1 text-sm font-semibold text-slate-800">{{ __('Account overview') }}</span>
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </nav>
            </x-ui.card>
        </div>
    </div>
@endsection
