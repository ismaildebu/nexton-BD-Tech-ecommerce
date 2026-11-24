@extends('layouts.admin')
@section('title', __('Shipping'))
@section('content')
    <x-ui.page-header
        :title="__('Shipping')"
        :eyebrow="__('Fulfillment operations')"
        :description="__('Create shipments and keep tracking status up to date.')"
    />

    <x-ui.card class="mb-6">
        <div class="mb-5 flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </span>
            <div>
                <h2 class="ui-section-title">{{ __('Create shipment') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Assign a carrier and tracking number to an eligible order.') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.shipping.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            @csrf
            <x-form.select name="order_id" :label="__('Order')" required>
                <option value="">{{ __('Select order') }}</option>
                @foreach ($orders as $order)
                    <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>{{ $order->order_number }} ({{ $order->status }})</option>
                @endforeach
            </x-form.select>

            <x-form.select name="vendor_id" :label="__('Vendor')" :hint="__('Select a vendor for mixed orders.')">
                <option value="">{{ __('Select vendor for mixed order') }}</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->business_name }}</option>
                @endforeach
            </x-form.select>

            <x-form.input name="carrier" :label="__('Carrier')" required maxlength="128" />

            <x-form.input name="tracking_number" :label="__('Tracking number')" required maxlength="128" />

            <div class="flex items-end">
                <x-ui.button type="submit" variant="primary" class="w-full">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                    {{ __('Create shipment') }}
                </x-ui.button>
            </div>
        </form>
        @if ($orders->isEmpty())
            <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ __('No eligible orders found.') }}</p>
        @endif
    </x-ui.card>

    @if ($shippings->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                :title="__('No shipments found')"
                :description="__('Create a shipment above to begin tracking fulfillment.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </x-slot:icon>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Shipments and fulfillment status')">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Order') }}</th>
                    <th scope="col">{{ __('Vendor') }}</th>
                    <th scope="col">{{ __('Carrier') }}</th>
                    <th scope="col">{{ __('Tracking') }}</th>
                    <th scope="col">{{ __('Shipment status') }}</th>
                    <th scope="col" class="text-right">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            @foreach ($shippings as $shipment)
                <tr>
                    <td class="min-w-44">
                        <a href="{{ route('admin.orders.show', $shipment->order) }}" class="ui-link font-mono text-xs">{{ $shipment->order->order_number }}</a>
                        <span class="mt-1 block text-xs text-slate-500">{{ $shipment->order->status }}</span>
                    </td>
                    <td>{{ $shipment->vendor?->business_name ?: '—' }}</td>
                    <td class="font-medium text-slate-700">{{ $shipment->carrier ?: '—' }}</td>
                    <td class="max-w-48 break-all font-mono text-xs">{{ $shipment->tracking_number ?: '—' }}</td>
                    <td>
                        <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $shipment->status))) }}</x-ui.status>
                    </td>
                    <td class="text-right">
                        @if ($shipment->status === 'pending')
                            <form method="POST" action="{{ route('admin.shipping.transition', $shipment) }}">
                                @csrf
                                <input type="hidden" name="status" value="shipped">
                                <x-ui.button type="submit" variant="success" size="sm" class="min-h-10">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    {{ __('Mark shipped') }}
                                </x-ui.button>
                            </form>
                        @elseif ($shipment->status === 'shipped')
                            <form method="POST" action="{{ route('admin.shipping.transition', $shipment) }}">
                                @csrf
                                <input type="hidden" name="status" value="delivered">
                                <x-ui.button type="submit" variant="secondary" size="sm" class="min-h-10">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Mark delivered') }}
                                </x-ui.button>
                            </form>
                        @else
                            <span class="text-xs text-slate-400">{{ __('No action available') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="ui-pagination mt-5">{{ $shippings->links() }}</div>
    @endif
@endsection