@extends('layouts.frontend')

@section('title', __('Order :number', ['number' => $order->order_number]))

@section('content')
    @php
        $orderStatus = strtolower((string) $order->status);
        $isCancelled = $orderStatus === 'cancelled';
        $currentStep = match ($orderStatus) {
            'processing', 'shipped' => 1,
            'completed', 'delivered' => 2,
            default => 0,
        };
        $timelineSteps = $isCancelled
            ? [['cancelled', __('Cancelled'), __('This order was cancelled.')]]
            : [
                ['placed', __('Order placed'), __('We have received your order.')],
                ['processing', __('Processing'), __('Your order is being prepared.')],
                ['completed', __('Completed'), __('Your order is complete.')],
            ];
        $shippingAddress = $order->shipping_address ?? [];
    @endphp

    <div class="ui-page-narrow">
        <a href="{{ route('orders.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-brand-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5l-7.5-7.5 7.5-7.5M3 12h18"/></svg>
            {{ __('Back to orders') }}
        </a>

        <x-ui.page-header :title="__('Order :number', ['number' => $order->order_number])"
            :description="__('Placed on :date', ['date' => $order->created_at->translatedFormat('d M Y, h:i A')])"
            :eyebrow="__('Order details')">
            <x-slot:action>
                <x-ui.status :status="$order->status" />
                <x-ui.status :status="$order->payment_status" />
            </x-slot:action>
        </x-ui.page-header>

        <x-ui.card class="mb-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <h2 class="ui-section-title">{{ __('Order progress') }}</h2>
                <span class="text-xs font-medium text-slate-500">{{ __('Latest status') }}: <x-ui.status :status="$order->status" /></span>
            </div>
            <ol class="grid gap-3 sm:grid-cols-3">
                @foreach ($timelineSteps as $index => [$key, $title, $description])
                    @php
                        $isCancelledStep = $key === 'cancelled';
                        $isComplete = ! $isCancelledStep && ($index < $currentStep || ($index === $currentStep && in_array($orderStatus, ['completed', 'delivered'], true)));
                        $isCurrent = ! $isCancelledStep && $index === $currentStep && ! $isComplete;
                    @endphp
                    <li @if($isCurrent || $isCancelledStep) aria-current="step" @endif
                        class="rounded-xl border p-4 {{ $isCancelledStep ? 'border-rose-200 bg-rose-50' : ($isComplete ? 'border-emerald-200 bg-emerald-50/60' : ($isCurrent ? 'border-brand-200 bg-brand-50/60' : 'border-slate-200 bg-slate-50/60')) }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $isCancelledStep ? 'bg-rose-600 text-white' : ($isComplete ? 'bg-emerald-600 text-white' : ($isCurrent ? 'bg-brand-500 text-white' : 'bg-white text-slate-400 ring-1 ring-slate-200')) }}">
                                @if ($isComplete)
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @elseif ($isCancelledStep)
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </span>
                            <p class="text-sm font-bold {{ $isCancelledStep ? 'text-rose-900' : 'text-slate-900' }}">{{ $title }}</p>
                        </div>
                        <p class="mt-2 pl-11 text-xs leading-5 text-slate-500">{{ $description }}</p>
                    </li>
                @endforeach
            </ol>
        </x-ui.card>

        @if ($order->status === 'pending' && $order->payment_status === 'unpaid')
            <x-ui.card class="mb-6 border-rose-200 bg-rose-50/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-rose-900">{{ __('Need to change your mind?') }}</h2>
                        <p class="mt-1 text-sm leading-6 text-rose-800">{{ __('Pending orders with unpaid payment can be cancelled.') }}</p>
                    </div>
                    <form method="POST" action="{{ route('orders.cancel', $order) }}" x-data="{ pending: false }" @submit="pending = true">
                        @csrf
                        <x-ui.button type="submit" variant="danger" :disabled="false" x-bind:disabled="pending">
                            <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
                            <span x-text="pending ? @js(__('Cancelling...')) : @js(__('Cancel order'))">{{ __('Cancel order') }}</span>
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.card>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-[1.5fr_0.8fr]">
            <div class="space-y-6">
                <x-ui.card :padding="false">
                    <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                        <h2 class="ui-section-title">{{ __('Items') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Products included in this order.') }}</p>
                    </div>
                    @if ($order->items->isEmpty())
                        <p class="p-6 text-sm text-slate-500">{{ __('No items found for this order.') }}</p>
                    @else
                        <x-ui.table :caption="__('Order items')" class="[&_table]:min-w-[560px]">
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
                                    <td>{{ $item->quantity }}</td>
                                    <td class="whitespace-nowrap text-right font-semibold text-slate-900">৳ {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    @endif
                </x-ui.card>

                <x-ui.card>
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18v13H3V6zm0 4h18M6 15h3"/></svg>
                        </span>
                        <h2 class="ui-section-title">{{ __('Payments') }}</h2>
                    </div>
                    @forelse ($order->payments as $payment)
                        <div class="flex flex-col gap-3 border-t border-slate-100 py-4 first:border-0 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-bold capitalize text-slate-900">{{ $payment->gateway ?: '—' }}</p>
                                @if ($payment->transaction_id)
                                    <p class="mt-1 break-all text-xs text-slate-500">{{ $payment->transaction_id }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">{{ $payment->created_at->translatedFormat('d M Y, h:i A') }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3 sm:flex-col sm:items-end">
                                <span class="text-sm font-bold text-slate-900">৳ {{ number_format($payment->amount, 2) }}</span>
                                <x-ui.status :status="$payment->status" />
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">{{ __('No payment records.') }}</p>
                    @endforelse
                </x-ui.card>

                <x-ui.card>
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v11H3V6zm11 4h4l3 4v3h-7v-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>
                        </span>
                        <h2 class="ui-section-title">{{ __('Shipments') }}</h2>
                    </div>
                    @forelse ($order->shippings as $shipment)
                        <div class="flex flex-col gap-3 border-t border-slate-100 py-4 first:border-0 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900">{{ $shipment->carrier ?: '—' }}</p>
                                <p class="mt-1 break-all text-xs text-slate-500">{{ __('Tracking') }}: {{ $shipment->tracking_number ?: '—' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $shipment->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                            <x-ui.status :status="$shipment->status" />
                        </div>
                    @empty
                        <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">{{ __('No shipment records.') }}</p>
                    @endforelse
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-ui.card>
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12zM14.5 9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                        </span>
                        <h2 class="ui-section-title">{{ __('Shipping address') }}</h2>
                    </div>
                    <address class="text-sm not-italic leading-6 text-slate-600">
                        <span class="block font-bold text-slate-900">{{ data_get($shippingAddress, 'full_name', '—') }}</span>
                        <span class="block">{{ data_get($shippingAddress, 'phone', '—') }}</span>
                        <span class="block">{{ data_get($shippingAddress, 'address_line', '—') }}</span>
                        <span class="block">{{ collect([data_get($shippingAddress, 'upazila'), data_get($shippingAddress, 'district')])->filter()->implode(', ') ?: '—' }}</span>
                        <span class="block">{{ trim(implode(' ', array_filter([data_get($shippingAddress, 'division'), data_get($shippingAddress, 'postal_code')]))) ?: '—' }}</span>
                    </address>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="ui-section-title">{{ __('Order totals') }}</h2>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Subtotal') }}</dt><dd class="font-semibold text-slate-800">৳ {{ number_format($order->subtotal, 2) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Shipping') }}</dt><dd class="font-semibold text-slate-800">৳ {{ number_format($order->shipping_total, 2) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Discount') }}</dt><dd class="font-semibold text-slate-800">− ৳ {{ number_format($order->discount_total, 2) }}</dd></div>
                        <div class="flex justify-between gap-3 border-t border-slate-200 pt-4 text-base font-extrabold"><dt class="text-slate-950">{{ __('Total') }}</dt><dd class="text-slate-950">৳ {{ number_format($order->total, 2) }}</dd></div>
                    </dl>
                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Payment status') }}</p>
                        <x-ui.status :status="$order->payment_status" class="mt-2" />
                    </div>
                </x-ui.card>

                <x-ui.card class="bg-slate-50/70">
                    <h2 class="text-sm font-bold text-slate-900">{{ __('More activity') }}</h2>
                    <div class="mt-4 flex flex-col gap-2">
                        <x-ui.button :href="route('payments.index')" variant="outline" size="sm" class="w-full">{{ __('All payments') }}</x-ui.button>
                        <x-ui.button :href="route('shipping.index')" variant="outline" size="sm" class="w-full">{{ __('All shipments') }}</x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection
