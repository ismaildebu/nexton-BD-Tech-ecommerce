@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('content')
    <x-ui.page-header
        :title="__('Dashboard')"
        :eyebrow="__('Operations overview')"
        :description="__('Monitor revenue, orders, customers and inventory from one workspace.')"
    >
        <x-slot:action>
            <x-ui.button :href="route('admin.settings.edit')" variant="outline" size="sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.431.992l1.003.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.457c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.942-1.11.942h-2.594c-.55 0-1.019-.399-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.457a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.457c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/></svg>
                {{ __('Site settings') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.card :padding="false" class="ui-stat relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 to-brand-300"></div>
            <div class="flex items-start justify-between gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
                <x-ui.badge tone="success">{{ __('Paid & completed') }}</x-ui.badge>
            </div>
            <p class="ui-stat-label mt-5">{{ __('Recognized revenue') }}</p>
            <p class="ui-stat-value">৳ {{ number_format($revenue, 2) }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('Revenue from completed, paid orders.') }}</p>
        </x-ui.card>

        <x-ui.card :padding="false" class="ui-stat">
            <div class="flex items-start justify-between gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </span>
                <x-ui.badge tone="info">{{ __('All time') }}</x-ui.badge>
            </div>
            <p class="ui-stat-label mt-5">{{ __('Total orders') }}</p>
            <p class="ui-stat-value">{{ number_format($orders) }}</p>
            <a href="{{ route('admin.orders.index') }}" class="ui-link mt-4 inline-flex items-center gap-1.5 text-xs">
                {{ __('Manage orders') }}
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>
        </x-ui.card>

        <x-ui.card :padding="false" class="ui-stat">
            <div class="flex items-start justify-between gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <x-ui.badge tone="accent">{{ __('Customers') }}</x-ui.badge>
            </div>
            <p class="ui-stat-label mt-5">{{ __('Non-admin customers') }}</p>
            <p class="ui-stat-value">{{ number_format($customers) }}</p>
            <a href="{{ route('admin.customers.index') }}" class="ui-link mt-4 inline-flex items-center gap-1.5 text-xs">
                {{ __('View customers') }}
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>
        </x-ui.card>

        <x-ui.card :padding="false" class="ui-stat">
            <div class="flex items-start justify-between gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h-1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                <x-ui.badge :tone="$pendingVendors > 0 ? 'warning' : 'neutral'">
                    {{ number_format($pendingVendors) }} {{ __('pending') }}
                </x-ui.badge>
            </div>
            <p class="ui-stat-label mt-5">{{ __('Marketplace vendors') }}</p>
            <p class="ui-stat-value">{{ number_format($vendors) }}</p>
            <a href="{{ route('admin.vendors.index') }}" class="ui-link mt-4 inline-flex items-center gap-1.5 text-xs">
                {{ __('Review vendors') }}
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>
        </x-ui.card>
    </div>

    <div class="mt-7 grid grid-cols-1 gap-6 2xl:grid-cols-2">
        <x-ui.card :padding="false" class="min-w-0 overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div>
                    <h2 class="ui-section-title">{{ __('Product inventory') }}</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Latest products and current stock levels') }}</p>
                </div>
                <x-ui.button :href="route('admin.products.index')" variant="outline" size="sm" class="self-start sm:self-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4"/></svg>
                    {{ __('Manage products') }}
                </x-ui.button>
            </div>

            @if ($inventoryProducts->isEmpty())
                <x-ui.empty-state
                    :title="__('No products found in inventory')"
                    :description="__('Products will appear here with their latest stock levels.')"
                >
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            @else
                <x-ui.table :caption="__('Latest products and stock levels')" class="rounded-none border-0 shadow-none">
                    <x-slot:head>
                        <tr>
                            <th scope="col">{{ __('Product') }}</th>
                            <th scope="col">{{ __('Stock') }}</th>
                            <th scope="col">{{ __('Price') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($inventoryProducts as $product)
                        @php
                            $quantity = $product->inventory?->quantity ?? 0;
                            $threshold = $product->inventory?->low_stock_threshold ?? 5;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.inventory.index') }}" class="font-semibold text-slate-900 hover:text-brand-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">{{ $product->name }}</a>
                                <span class="mt-1 block text-xs text-slate-400">{{ $product->category->name ?? __('Uncategorized') }}</span>
                            </td>
                            <td class="whitespace-nowrap font-semibold text-slate-700">{{ number_format($quantity) }} {{ __('units') }}</td>
                            <td class="whitespace-nowrap font-semibold text-slate-900">৳ {{ number_format($product->price, 2) }}</td>
                            <td>
                                <x-ui.badge :tone="$quantity === 0 ? 'danger' : ($quantity <= $threshold ? 'warning' : 'success')">
                                    {{ $quantity === 0 ? __('Out of stock') : ($quantity <= $threshold ? __('Low stock') : __('In stock')) }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif

            <div class="border-t border-slate-100 p-4 text-center">
                <a href="{{ route('admin.inventory.index') }}" class="ui-link inline-flex items-center gap-1.5 text-xs">
                    {{ __('Adjust inventory') }}
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </div>
        </x-ui.card>

        <x-ui.card :padding="false" class="min-w-0 overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div>
                    <h2 class="ui-section-title">{{ __('Recent orders') }}</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Latest customer orders') }}</p>
                </div>
                <x-ui.button :href="route('admin.orders.index')" variant="outline" size="sm" class="self-start sm:self-auto">
                    {{ __('View all orders') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </x-ui.button>
            </div>

            @if ($recentOrders->isEmpty())
                <x-ui.empty-state
                    :title="__('No orders recorded yet')"
                    :description="__('New customer orders will appear here as soon as they are placed.')"
                >
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            @else
                <x-ui.table :caption="__('Recent customer orders')" class="rounded-none border-0 shadow-none">
                    <x-slot:head>
                        <tr>
                            <th scope="col">{{ __('Customer') }}</th>
                            <th scope="col">{{ __('Order') }}</th>
                            <th scope="col">{{ __('Amount') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($recentOrders as $order)
                        <tr>
                            <td>
                                <span class="block max-w-[10rem] truncate font-semibold text-slate-900">{{ $order->user->name ?? __('Customer') }}</span>
                                <time datetime="{{ $order->created_at->toIso8601String() }}" class="mt-1 block text-xs text-slate-400">{{ $order->created_at->format('d M Y') }}</time>
                            </td>
                            <td class="whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="ui-link font-mono text-xs">{{ $order->order_number }}</a>
                            </td>
                            <td class="whitespace-nowrap font-semibold text-slate-900">৳ {{ number_format($order->total, 2) }}</td>
                            <td>
                                <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $order->status))) }}</x-ui.status>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif

            <div class="border-t border-slate-100 p-4 text-center">
                <a href="{{ route('admin.orders.index') }}" class="ui-link inline-flex items-center gap-1.5 text-xs">
                    {{ __('View all') }} {{ number_format($orders) }} {{ __('orders') }}
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </div>
        </x-ui.card>
    </div>
@endsection
