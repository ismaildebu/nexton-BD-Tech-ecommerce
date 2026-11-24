@extends('layouts.frontend')

@section('title', __('Shipping'))

@section('content')
    <div class="ui-page">
        <x-ui.page-header :title="__('Shipment tracking')" :description="__('Find your carrier, tracking number and the latest delivery status for each shipment.')"
            :eyebrow="__('Shopping activity')">
            <x-slot:action><x-ui.button :href="route('orders.index')" variant="outline">{{ __('View orders') }}</x-ui.button></x-slot:action>
        </x-ui.page-header>

        @if ($shippings->isEmpty())
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No shipments yet')" :description="__('Tracking details will appear here when a shipment is created for one of your orders.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v11H3V6zm11 4h4l3 4v3h-7v-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>
                    </x-slot:icon>
                    <x-slot:action><x-ui.button :href="route('orders.index')">{{ __('View your orders') }}</x-ui.button></x-slot:action>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <div class="hidden md:block">
                <x-ui.table :caption="__('Shipment tracking')" class="[&_table]:min-w-[780px]">
                    <x-slot:head>
                        <tr>
                            <th scope="col">{{ __('Updated') }}</th>
                            <th scope="col">{{ __('Order') }}</th>
                            <th scope="col">{{ __('Carrier') }}</th>
                            <th scope="col">{{ __('Tracking number') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('Actions') }}</span></th>
                        </tr>
                    </x-slot:head>
                    @foreach ($shippings as $shipment)
                        <tr>
                            <td class="whitespace-nowrap">{{ $shipment->created_at->translatedFormat('d M Y') }}</td>
                            <td><a href="{{ route('orders.show', $shipment->order_id) }}" class="font-bold text-slate-900 hover:text-brand-600">{{ $shipment->order?->order_number ?? __('Order #:id', ['id' => $shipment->order_id]) }}</a></td>
                            <td>{{ $shipment->carrier ?: '—' }}</td>
                            <td class="font-mono text-xs">{{ $shipment->tracking_number ?: '—' }}</td>
                            <td><x-ui.status :status="$shipment->status" /></td>
                            <td class="text-right"><x-ui.button :href="route('orders.show', $shipment->order_id)" variant="outline" size="sm">{{ __('View order') }}</x-ui.button></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>

            <div class="space-y-4 md:hidden">
                @foreach ($shippings as $shipment)
                    <x-ui.card>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a href="{{ route('orders.show', $shipment->order_id) }}" class="text-sm font-extrabold text-slate-950 hover:text-brand-600">{{ $shipment->order?->order_number ?? __('Order #:id', ['id' => $shipment->order_id]) }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $shipment->created_at->translatedFormat('d M Y, h:i A') }}</p>
                            </div>
                            <x-ui.status :status="$shipment->status" />
                        </div>
                        <dl class="mt-4 space-y-3 border-t border-slate-100 pt-4 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Carrier') }}</dt><dd class="text-right font-semibold text-slate-800">{{ $shipment->carrier ?: '—' }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ __('Tracking number') }}</dt><dd class="break-all text-right font-mono text-xs font-semibold text-slate-800">{{ $shipment->tracking_number ?: '—' }}</dd></div>
                        </dl>
                        <x-ui.button :href="route('orders.show', $shipment->order_id)" variant="outline" size="sm" class="mt-4 w-full">{{ __('View order') }}</x-ui.button>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="ui-pagination mt-6">{{ $shippings->links() }}</div>
        @endif
    </div>
@endsection
