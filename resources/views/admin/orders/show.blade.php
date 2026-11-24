@extends('layouts.admin')
@section('title', __('Order :number', ['number' => $order->order_number]))
@section('content')
    <a href="{{ route('admin.orders.index') }}" class="ui-link mb-4 inline-flex min-h-11 items-center gap-2 text-sm">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6 6-6-6 6 6"/></svg>
        {{ __('Back to orders') }}
    </a>

    <x-ui.page-header
        :title="$order->order_number"
        :eyebrow="__('Order details')"
        :description="__('Placed :date for :name (:email)', [
            'date' => $order->created_at->format('d M Y, H:i'),
            'name' => $order->user?->name ?? __('Customer'),
            'email' => $order->user?->email ?? __('No email provided'),
        ])"
    >
        <x-slot:action>
            <div class="flex flex-wrap items-center gap-2">
                <span class="sr-only">{{ __('Order status') }}</span>
                <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $order->status))) }}</x-ui.status>
                <span class="sr-only">{{ __('Payment status') }}</span>
                <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $order->payment_status))) }}</x-ui.status>
            </div>
        </x-slot:action>
    </x-ui.page-header>

    <x-ui.card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="ui-section-title">{{ __('Order workflow') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Follow the order from placement through completion.') }}</p>
            </div>
            <x-ui.badge :tone="$order->status === 'cancelled' ? 'danger' : 'accent'">{{ __(ucfirst($order->status)) }}</x-ui.badge>
        </div>

        @php
            $workflow = [
                ['status' => 'pending', 'label' => __('Pending')],
                ['status' => 'processing', 'label' => __('Processing')],
                ['status' => 'completed', 'label' => __('Completed')],
            ];
            $currentStep = array_search($order->status, array_column($workflow, 'status'), true);
        @endphp

        @if ($order->status === 'cancelled')
            <x-ui.alert tone="error" class="mt-5" :title="__('This order was cancelled')">
                {{ __('Cancellation restores stock and refunds any paid COD payment.') }}
            </x-ui.alert>
        @else
            <ol class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="{{ __('Order progress') }}">
                @foreach ($workflow as $index => $step)
                    @php
                        $isCurrent = $currentStep !== false && $index === $currentStep;
                        $isComplete = $currentStep !== false && $index < $currentStep;
                        $stepClasses = $isCurrent
                            ? 'border-accent-200 bg-accent-50'
                            : ($isComplete ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50');
                        $circleClasses = $isCurrent
                            ? 'bg-accent-600 text-white'
                            : ($isComplete ? 'bg-emerald-600 text-white' : 'bg-white text-slate-400 ring-1 ring-slate-200');
                    @endphp
                    <li class="flex items-center gap-3 rounded-xl border p-3 {{ $stepClasses }}">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $circleClasses }}">
                            @if ($isComplete)
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-slate-900">{{ $step['label'] }}</span>
                            <span class="mt-0.5 block text-[11px] text-slate-500">
                                {{ $isCurrent ? __('Current status') : ($isComplete ? __('Completed') : __('Upcoming')) }}
                            </span>
                        </span>
                    </li>
                @endforeach
            </ol>
        @endif

        <div class="mt-5 flex flex-wrap gap-3 border-t border-slate-100 pt-5">
            @if ($order->status === 'pending')
                <form method="POST" action="{{ route('admin.orders.transition', $order) }}">
                    @csrf
                    <input type="hidden" name="status" value="processing">
                    <x-ui.button type="submit" variant="primary" size="sm" class="min-h-10">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        {{ __('Start processing') }}
                    </x-ui.button>
                </form>
            @endif
            @if (in_array($order->status, ['pending', 'processing'], true))
                <form method="POST" action="{{ route('admin.orders.transition', $order) }}">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <x-ui.button type="submit" variant="outline" size="sm" class="min-h-10 text-rose-700 hover:border-rose-200 hover:bg-rose-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        {{ __('Cancel order') }}
                    </x-ui.button>
                </form>
            @endif
            @if (! in_array($order->status, ['pending', 'processing'], true))
                <p class="text-sm text-slate-500">{{ __('No order status actions are available.') }}</p>
            @endif
        </div>
        @if ($order->status !== 'cancelled')
            <p class="mt-3 text-xs leading-5 text-slate-500">{{ __('Cancellation restores stock and refunds any paid COD payment.') }}</p>
        @endif
    </x-ui.card>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="min-w-0 space-y-6 lg:col-span-2">
            <x-ui.card :padding="false" class="overflow-hidden">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h2 class="ui-section-title">{{ __('Items') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Products included in this order.') }}</p>
                </div>
                @if ($order->items->isEmpty())
                    <x-ui.empty-state :title="__('No items found')" :description="__('This order does not contain any line items.')" />
                @else
                    <x-ui.table :caption="__('Order line items')" class="rounded-none border-0 shadow-none">
                        <x-slot:head>
                            <tr>
                                <th scope="col">{{ __('Product') }}</th>
                                <th scope="col">{{ __('Price') }}</th>
                                <th scope="col">{{ __('Quantity') }}</th>
                                <th scope="col" class="text-right">{{ __('Total') }}</th>
                            </tr>
                        </x-slot:head>
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $item->name }}</td>
                                <td class="whitespace-nowrap">৳ {{ number_format($item->unit_price, 2) }}</td>
                                <td class="whitespace-nowrap">{{ number_format($item->quantity) }}</td>
                                <td class="whitespace-nowrap text-right font-semibold text-slate-900">৳ {{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>

            <x-ui.card :padding="false" class="overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <h2 class="ui-section-title">{{ __('Payments') }}</h2>
                    <x-ui.button :href="route('admin.payments.index')" variant="outline" size="sm" class="self-start sm:self-auto">{{ __('Manage payments') }}</x-ui.button>
                </div>
                @if ($order->payments->isEmpty())
                    <x-ui.empty-state :title="__('No payment records')" :description="__('Payment activity for this order will appear here.')" />
                @else
                    <x-ui.table :caption="__('Payments recorded for this order')" class="rounded-none border-0 shadow-none">
                        <x-slot:head>
                            <tr>
                                <th scope="col">{{ __('Gateway') }}</th>
                                <th scope="col">{{ __('Transaction ID') }}</th>
                                <th scope="col">{{ __('Amount') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                            </tr>
                        </x-slot:head>
                        @foreach ($order->payments as $payment)
                            <tr>
                                <td class="font-semibold uppercase text-slate-700">{{ $payment->gateway ?: '—' }}</td>
                                <td class="max-w-48 break-all text-xs">{{ $payment->transaction_id ?: '—' }}</td>
                                <td class="whitespace-nowrap font-semibold text-slate-900">৳ {{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $payment->status))) }}</x-ui.status>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>

            <x-ui.card :padding="false" class="overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <h2 class="ui-section-title">{{ __('Shipments') }}</h2>
                    <x-ui.button :href="route('admin.shipping.index')" variant="outline" size="sm" class="self-start sm:self-auto">{{ __('Manage shipping') }}</x-ui.button>
                </div>
                @if ($order->shippings->isEmpty())
                    <x-ui.empty-state :title="__('No shipment records')" :description="__('Shipment activity for this order will appear here.')" />
                @else
                    <x-ui.table :caption="__('Shipments recorded for this order')" class="rounded-none border-0 shadow-none">
                        <x-slot:head>
                            <tr>
                                <th scope="col">{{ __('Carrier') }}</th>
                                <th scope="col">{{ __('Tracking number') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                            </tr>
                        </x-slot:head>
                        @foreach ($order->shippings as $shipment)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $shipment->carrier ?: '—' }}</td>
                                <td class="break-all font-mono text-xs">{{ $shipment->tracking_number ?: '—' }}</td>
                                <td>
                                    <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $shipment->status))) }}</x-ui.status>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
                    </span>
                    <h2 class="ui-section-title">{{ __('Shipping address') }}</h2>
                </div>
                @php ($address = $order->shipping_address ?? [])
                <address class="text-sm not-italic leading-6 text-slate-600">
                    <span class="block font-semibold text-slate-900">{{ data_get($address, 'full_name', '—') }}</span>
                    <span class="block">{{ data_get($address, 'phone', '—') }}</span>
                    <span class="block">{{ data_get($address, 'address_line', '—') }}</span>
                    <span class="block">{{ data_get($address, 'upazila', '—') }}, {{ data_get($address, 'district', '—') }}</span>
                    <span class="block">{{ data_get($address, 'division', '—') }} {{ data_get($address, 'postal_code', '') }}</span>
                </address>
            </x-ui.card>

            <x-ui.card>
                <h2 class="ui-section-title mb-4">{{ __('Order totals') }}</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Subtotal') }}</dt><dd class="font-semibold text-slate-800">৳ {{ number_format($order->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Shipping') }}</dt><dd class="font-semibold text-slate-800">৳ {{ number_format($order->shipping_total, 2) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Discount') }}</dt><dd class="font-semibold text-emerald-700">− ৳ {{ number_format($order->discount_total, 2) }}</dd></div>
                    <div class="flex justify-between gap-4 border-t border-slate-200 pt-4 text-base font-bold text-slate-950"><dt>{{ __('Total') }}</dt><dd>৳ {{ number_format($order->total, 2) }}</dd></div>
                </dl>
            </x-ui.card>
        </div>
    </div>
@endsection
