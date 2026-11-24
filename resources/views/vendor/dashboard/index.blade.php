@extends('layouts.vendor')
@section('title', __('Vendor Dashboard'))
@section('content')
    @if ($vendor)
        <x-ui.page-header
            :title="$vendor->business_name"
            :description="__('Monitor your catalog and keep your vendor workspace ready for business.')"
            :eyebrow="__('Vendor Dashboard')"
        >
            <x-slot:action>
                <x-ui.status>{{ $vendor->verification_status->label() }}</x-ui.status>
                <x-ui.badge :tone="$vendor->status->value === 'active' ? 'success' : ($vendor->status->value === 'blocked' || $vendor->status->value === 'suspended' ? 'danger' : 'neutral')">
                    {{ $vendor->status->label() }}
                </x-ui.badge>
            </x-slot:action>
        </x-ui.page-header>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.card class="ui-stat relative overflow-hidden">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-stat-label">{{ __('Total products') }}</p>
                        <p class="ui-stat-value tabular-nums">{{ number_format($stats['products'] ?? 0) }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Products in your catalog') }}</p>
                    </div>
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                </div>
                <a href="{{ route('vendor.products.index') }}" class="mt-5 inline-flex min-h-11 items-center text-sm font-bold text-brand-600 hover:text-brand-700 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                    {{ __('Manage products') }}
                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </x-ui.card>

            <x-ui.card class="ui-stat relative overflow-hidden">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-stat-label">{{ __('Active products') }}</p>
                        <p class="ui-stat-value tabular-nums text-emerald-700">{{ number_format($stats['activeProducts'] ?? 0) }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Currently visible in your catalog') }}</p>
                    </div>
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-accent-50 text-accent-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m-3-9a9 9 0 11-6.7 3M3 4v5h5"/></svg>
                    </span>
                </div>
                <a href="{{ route('vendor.inventory.index') }}" class="mt-5 inline-flex min-h-11 items-center text-sm font-bold text-accent-700 hover:text-accent-800 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                    {{ __('Review inventory') }}
                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </x-ui.card>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <x-ui.card :padding="false">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="ui-section-title">{{ __('Quick actions') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Jump into your most common workspace tasks.') }}</p>
                </div>
                <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6">
                    <x-ui.button :href="route('vendor.products.create')" variant="primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add product') }}
                    </x-ui.button>
                    <x-ui.button :href="route('vendor.orders.index')" variant="outline">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        {{ __('View orders') }}
                    </x-ui.button>
                    <x-ui.button :href="route('vendor.documents.index', $vendor)" variant="outline">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3h7l5 5v13H7V3z"/></svg>
                        {{ __('Documents') }}
                    </x-ui.button>
                    <x-ui.button :href="route('vendor.bank-accounts.index', $vendor)" variant="outline">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        {{ __('Bank accounts') }}
                    </x-ui.button>
                    @if (auth()->id() === $vendor->owner_user_id)
                        <x-ui.button :href="route('vendor.staff.index', $vendor)" variant="outline">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M14 4a4 4 0 010 8M10 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                            {{ __('Manage staff') }}
                        </x-ui.button>
                        <x-ui.button :href="route('vendor.settings.edit', $vendor)" variant="outline">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4V4zm5 5h6m-6 4h6"/></svg>
                            {{ __('Business profile') }}
                        </x-ui.button>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-ink-900 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M12 3l9 4v5c0 5-3.5 8-9 9-5.5-1-9-4-9-9V7l9-4z"/></svg>
                    </span>
                    <div>
                        <h2 class="ui-section-title">{{ __('Workspace status') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Vendor code') }}: {{ $vendor->vendor_code }}</p>
                    </div>
                </div>
                <dl class="mt-5 space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Verification') }}</dt>
                        <dd><x-ui.status>{{ $vendor->verification_status->label() }}</x-ui.status></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">{{ __('Account') }}</dt>
                        <dd class="font-semibold text-slate-800">{{ $vendor->status->label() }}</dd>
                    </div>
                </dl>
                <x-ui.button :href="route('vendor.registration.status')" variant="outline" class="mt-6 w-full">
                    {{ __('View application status') }}
                </x-ui.button>
            </x-ui.card>
        </div>
    @else
        <x-ui.card :padding="false">
            <x-ui.empty-state
                :title="__('No vendor profile yet.')"
                :description="__('Register your business to start selling and manage orders from one workspace.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6M9 10h.01M15 10h.01"/></svg>
                </x-slot:icon>
                <x-slot:action>
                    <x-ui.button :href="route('vendor.register.create')" variant="primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Register as vendor') }}
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @endif
@endsection
