@extends('layouts.frontend')

@section('title', __('Orders'))

@section('content')
    <div class="ui-page">
        <x-ui.page-header :title="__('Your orders')" :description="__('Track order progress, review payment status and revisit your purchases.')"
            :eyebrow="__('Shopping activity')">
            <x-slot:action>
                <x-ui.button :href="route('products.index')" variant="outline">{{ __('Continue shopping') }}</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @if ($orders->isEmpty())
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No orders yet')" :description="__('When you place an order, you can track it and view all its details here.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v18H3V3zm0 6h18M9 21V9"/></svg>
                    </x-slot:icon>
                    <x-slot:action><x-ui.button :href="route('products.index')">{{ __('Start shopping') }}</x-ui.button></x-slot:action>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <div class="hidden md:block">
                <x-ui.table :caption="__('Order history')" class="[&_table]:min-w-[800px]">
                    <x-slot:head>
                        <tr>
                            <th scope="col">{{ __('Order') }}</th>
                            <th scope="col">{{ __('Placed') }}</th>
                            <th scope="col">{{ __('Total') }}</th>
                            <th scope="col">{{ __('Order status') }}</th>
                            <th scope="col">{{ __('Payment') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('Actions') }}</span></th>
                        </tr>
                    </x-slot:head>
                    @foreach ($orders as $order)
                        <tr>
                            <td><a href="{{ route('orders.show', $order) }}" class="font-bold text-slate-900 hover:text-brand-600">{{ $order->order_number }}</a></td>
                            <td class="whitespace-nowrap">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap font-bold text-slate-900">৳ {{ number_format($order->total, 2) }}</td>
                            <td><x-ui.status :status="$order->status" /></td>
                            <td><x-ui.status :status="$order->payment_status" /></td>
                            <td class="text-right"><x-ui.button :href="route('orders.show', $order)" variant="outline" size="sm">{{ __('View details') }}</x-ui.button></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>

            <div class="space-y-4 md:hidden">
                @foreach ($orders as $order)
                    <x-ui.card>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Order') }}</p>
                                <a href="{{ route('orders.show', $order) }}" class="mt-1 block text-base font-extrabold text-slate-950 hover:text-brand-600">{{ $order->order_number }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                            <p class="text-lg font-extrabold text-slate-950">৳ {{ number_format($order->total, 2) }}</p>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                            <x-ui.status :status="$order->status" />
                            <x-ui.status :status="$order->payment_status" />
                        </div>
                        <x-ui.button :href="route('orders.show', $order)" variant="outline" size="sm" class="mt-4 w-full">{{ __('View order details') }}</x-ui.button>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="ui-pagination mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
