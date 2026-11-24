@extends('layouts.vendor')
@section('title', __('Order') . ' ' . $order->order_number)
@section('content')
    @php
        $vendorTotal = $order->items->sum(fn ($item) => (float) $item->line_total);
        $activeShipment = $order->shippings->firstWhere('status', 'pending') ?? $order->shippings->firstWhere('status', 'shipped');
        $address = $order->shipping_address ?? [];
    @endphp

    <x-ui.page-header
        :title="$order->order_number"
        :description="__('Review your items, delivery details, and fulfillment progress.')"
        :eyebrow="__('Order details')"
    >
        <x-slot:action>
            <x-ui.button :href="route('vendor.orders.index')" variant="outline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
                {{ __('Back to orders') }}
            </x-ui.button>
            <x-ui.status :status="$order->status" />
            <x-ui.status :status="$order->vendor_payment_status" />
        </x-slot:action>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="min-w-0 space-y-6">
            <x-ui.card :padding="false">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6">
                    <div>
                        <h2 class="ui-section-title">{{ __('Your items') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Only products from your vendor account are shown.') }}</p>
                    </div>
                    <span class="text-lg font-extrabold tabular-nums text-slate-950">৳ {{ number_format($vendorTotal, 2) }}</span>
                </div>
                <x-ui.table :caption="__('Your items in this order')" class="rounded-none border-0 shadow-none [&_table]:min-w-[36rem]">
                    <x-slot:head>
                        <tr>
                            <th scope="col">{{ __('Product') }}</th>
                            <th scope="col" class="text-right">{{ __('Unit price') }}</th>
                            <th scope="col" class="text-right">{{ __('Qty') }}</th>
                            <th scope="col" class="text-right">{{ __('Total') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $item->name }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">৳ {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="text-right tabular-nums">{{ $item->quantity }}</td>
                            <td class="whitespace-nowrap text-right font-semibold tabular-nums text-slate-900">৳ {{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v10H3V7zm11 4h4l3 3v3h-7v-6zM5 20a2 2 0 100-4 2 2 0 000 4zm13 0a2 2 0 100-4 2 2 0 000 4z"/></svg>
                    </span>
                    <div>
                        <h2 class="ui-section-title">{{ __('Fulfillment') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Create a shipment and keep its delivery status up to date.') }}</p>
                    </div>
                </div>

                <div class="mt-5">
                    @forelse ($order->shippings as $shipment)
                        <div class="flex flex-col gap-4 border-t border-slate-100 py-4 first:border-t-0 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900">{{ $shipment->carrier ?? __('Carrier not set') }}</p>
                                <p class="mt-1 break-all text-sm text-slate-500">{{ $shipment->tracking_number ?? __('No tracking number') }}</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-3">
                                <x-ui.status :status="$shipment->status" />
                                @if ($shipment->status === 'pending')
                                    <form method="POST" action="{{ route('vendor.orders.shipping.ship', [$order, $shipment]) }}">
                                        @csrf
                                        <x-ui.button type="submit" variant="primary">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v10H3V7zm11 4h4l3 3v3h-7v-6z"/></svg>
                                            {{ __('Mark shipped') }}
                                        </x-ui.button>
                                    </form>
                                @elseif ($shipment->status === 'shipped')
                                    <form method="POST" action="{{ route('vendor.orders.shipping.deliver', [$order, $shipment]) }}">
                                        @csrf
                                        <x-ui.button type="submit" variant="success">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            {{ __('Mark delivered') }}
                                        </x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-500">{{ __('No shipment created yet.') }}</p>
                    @endforelse
                </div>

                @if ($activeShipment === null && ! in_array($order->status, ['cancelled', 'completed'], true))
                    <form method="POST" action="{{ route('vendor.orders.shipping.store', $order) }}" class="mt-6 rounded-2xl bg-slate-50 p-5">
                        @csrf
                        <h3 class="font-bold text-slate-900">{{ __('Create shipment') }}</h3>
                        <p class="ui-help">{{ __('Enter the carrier and tracking number for this delivery.') }}</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <x-form.input name="carrier" :label="__('Carrier')" :value="old('carrier')" maxlength="128" required />
                            <x-form.input name="tracking_number" :label="__('Tracking number')" :value="old('tracking_number')" maxlength="128" required />
                        </div>
                        <x-ui.button type="submit" variant="primary" class="mt-5">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Create shipment') }}
                        </x-ui.button>
                    </form>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M14 4a4 4 0 010 8M10 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                    </span>
                    <h2 class="ui-section-title">{{ __('Delivery address') }}</h2>
                </div>
                <address class="mt-5 space-y-1 text-sm not-italic leading-6 text-slate-600">
                    <span class="block font-semibold text-slate-900">{{ $address['full_name'] ?? $order->user->name ?? '' }}</span>
                    <span class="block">{{ $address['phone'] ?? '' }}</span>
                    <span class="block">{{ $address['address_line'] ?? '' }}</span>
                    <span class="block">{{ collect([$address['upazila'] ?? null, $address['district'] ?? null, $address['postal_code'] ?? null])->filter()->implode(', ') }}</span>
                </address>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                    <h2 class="ui-section-title">{{ __('Payment (read only)') }}</h2>
                </div>
                <div class="mt-4">
                    @forelse ($order->payments as $payment)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 py-4 first:border-t-0 first:pt-0">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-700">{{ $payment->gateway ? strtoupper($payment->gateway) : __('Payment') }}</p>
                                <div class="mt-1.5"><x-ui.status :status="$payment->status" /></div>
                            </div>
                            <span class="font-semibold tabular-nums text-slate-900">৳ {{ number_format((float) $payment->amount, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No payment record.') }}</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection
