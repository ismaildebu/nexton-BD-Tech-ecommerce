@extends('layouts.frontend')

@section('title', __('Customer dashboard'))

@section('content')
    <div class="ui-page">
        <x-ui.page-header :title="__('Hello, :name', ['name' => $user->name])"
            :description="__('Welcome back. Here is the latest from your account.')" :eyebrow="__('Customer dashboard')">
            <x-slot:action>
                <x-ui.button :href="route('profile.edit')" variant="outline">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 1116 0"/></svg>
                    {{ __('Edit profile') }}
                </x-ui.button>
                <x-ui.button :href="route('products.index')">{{ __('Continue shopping') }}</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @if (($user->email !== null && ! $user->hasVerifiedEmail()) || ($user->phone !== null && ! $user->hasVerifiedPhone()))
            <x-ui.alert tone="warning" class="mb-6">
                {{ __('Complete contact verification to keep your account secure.') }}
                <a href="{{ route('verification.notice') }}" class="ml-1 font-bold underline underline-offset-2">{{ __('Verify now') }}</a>
            </x-ui.alert>
        @endif

        <div class="mb-8 grid gap-4 sm:grid-cols-3">
            @foreach ([
                [__('Total orders'), $ordersCount, route('orders.index'), __('View orders'), 'M3 3h18v18H3V3zm0 6h18M9 21V9', 'bg-brand-50 text-brand-600'],
                [__('Wishlist items'), $wishlistCount, route('wishlist.index'), __('Open wishlist'), 'M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 00-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 000-7.8z', 'bg-accent-50 text-accent-600'],
                [__('Saved addresses'), $addressCount, route('addresses.index'), __('Manage addresses'), 'M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12zM14.5 9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z', 'bg-sky-50 text-sky-600'],
            ] as [$label, $value, $href, $linkLabel, $path, $iconClasses])
                <x-ui.card class="transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="ui-stat-label">{{ $label }}</p>
                            <p class="ui-stat-value">{{ number_format($value) }}</p>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $iconClasses }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </span>
                    </div>
                    <a href="{{ $href }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-brand-600 transition hover:text-brand-700 hover:underline">
                        {{ $linkLabel }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </x-ui.card>
            @endforeach
        </div>

        <div class="grid min-w-0 items-start gap-6 lg:grid-cols-[1.6fr_0.8fr]">
            <x-ui.card :padding="false" class="min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">
                    <div>
                        <h2 class="ui-section-title">{{ __('Recent orders') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Your latest purchases at a glance.') }}</p>
                    </div>
                    @if ($recentOrders->isNotEmpty())
                        <x-ui.button :href="route('orders.index')" variant="ghost" size="sm">{{ __('View all') }}</x-ui.button>
                    @endif
                </div>

                @if ($recentOrders->isEmpty())
                    <x-ui.empty-state :title="__('Your first order starts here')" :description="__('Explore the store and find something you love. Your orders will appear here.')">
                        <x-slot:icon>
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v18H3V3zm0 6h18M9 21V9"/></svg>
                        </x-slot:icon>
                        <x-slot:action><x-ui.button :href="route('products.index')">{{ __('Browse products') }}</x-ui.button></x-slot:action>
                    </x-ui.empty-state>
                @else
                    <x-ui.table :caption="__('Recent orders')" class="[&_table]:min-w-[600px]">
                        <x-slot:head>
                            <tr>
                                <th scope="col">{{ __('Order') }}</th>
                                <th scope="col">{{ __('Placed') }}</th>
                                <th scope="col">{{ __('Total') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col"><span class="sr-only">{{ __('Actions') }}</span></th>
                            </tr>
                        </x-slot:head>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td class="font-bold text-slate-900">{{ $order->order_number }}</td>
                                <td class="whitespace-nowrap">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                                <td class="whitespace-nowrap font-semibold text-slate-900">৳ {{ number_format($order->total, 2) }}</td>
                                <td><x-ui.status :status="$order->status" /></td>
                                <td class="text-right"><x-ui.button :href="route('orders.show', $order)" variant="outline" size="sm">{{ __('View') }}</x-ui.button></td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>

            <div class="space-y-6">
                <x-ui.card>
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12zM14.5 9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                        </span>
                        <h2 class="ui-section-title">{{ __('Default address') }}</h2>
                    </div>
                    @if ($defaultAddress)
                        <address class="mt-4 text-sm not-italic leading-6 text-slate-600">
                            <span class="block font-bold text-slate-900">{{ $defaultAddress->full_name }}</span>
                            <span class="block">{{ $defaultAddress->address_line }}</span>
                            <span class="block">{{ collect([$defaultAddress->upazila, $defaultAddress->district, $defaultAddress->division, $defaultAddress->postal_code])->filter()->implode(', ') }}</span>
                            <span class="mt-2 block">{{ $defaultAddress->phone }}</span>
                        </address>
                        <x-ui.button :href="route('addresses.index')" variant="outline" size="sm" class="mt-5">{{ __('Manage addresses') }}</x-ui.button>
                    @else
                        <p class="mt-4 text-sm leading-6 text-slate-500">{{ __('Add a default address for quicker checkout.') }}</p>
                        <x-ui.button :href="route('addresses.create')" variant="outline" size="sm" class="mt-5">{{ __('Add address') }}</x-ui.button>
                    @endif
                </x-ui.card>

                <x-ui.card class="border-accent-200 bg-accent-50/50">
                    <h2 class="text-sm font-bold text-accent-900">{{ __('Your account, your way') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-accent-800/80">{{ __('Review your personal details, payments, shipments and product reviews whenever you need.') }}</p>
                    <x-ui.button :href="route('customer.account.index')" variant="secondary" size="sm" class="mt-4">{{ __('Account overview') }}</x-ui.button>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection
