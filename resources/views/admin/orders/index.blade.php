@extends('layouts.admin')
@section('title', __('Orders'))
@section('content')
    <x-ui.page-header
        :title="__('Orders')"
        :eyebrow="__('Order management')"
        :description="__('Review orders, payment progress and fulfillment status.')"
    >
        <x-slot:action>
            <x-ui.button :href="route('admin.payments.index')" variant="outline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                {{ __('Payments') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    @if ($orders->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                :title="__('No orders found')"
                :description="__('Customer orders will appear here as soon as they are placed.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </x-slot:icon>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Customer orders and lifecycle status')">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Number') }}</th>
                    <th scope="col">{{ __('Date') }}</th>
                    <th scope="col">{{ __('Total') }}</th>
                    <th scope="col">{{ __('Order status') }}</th>
                    <th scope="col">{{ __('Payment status') }}</th>
                    <th scope="col" class="text-right">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            @foreach ($orders as $order)
                <tr>
                    <td class="whitespace-nowrap font-mono font-semibold text-slate-900">{{ $order->order_number }}</td>
                    <td class="whitespace-nowrap">
                        <time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('d M Y') }}</time>
                    </td>
                    <td class="whitespace-nowrap font-semibold text-slate-900">৳ {{ number_format($order->total, 2) }}</td>
                    <td>
                        <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $order->status))) }}</x-ui.status>
                    </td>
                    <td>
                        <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $order->payment_status))) }}</x-ui.status>
                    </td>
                    <td class="text-right">
                        <x-ui.button :href="route('admin.orders.show', $order)" variant="outline" size="sm" class="min-h-10">
                            {{ __('View details') }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="ui-pagination mt-5">{{ $orders->links() }}</div>
    @endif
@endsection
