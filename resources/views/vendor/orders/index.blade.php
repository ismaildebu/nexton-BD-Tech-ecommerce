@extends('layouts.vendor')
@section('title', __('Vendor Orders'))
@section('content')
    <x-ui.page-header
        :title="__('Orders')"
        :description="__('Review orders containing your products, check payment status, and manage fulfillment.')"
        :eyebrow="__('Operations')"
    />

    @if ($orders->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state
                :title="__('No orders contain your products yet.')"
                :description="__('Orders will appear here when customers purchase products from your catalog.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </x-slot:icon>
                <x-slot:action>
                    <x-ui.button :href="route('vendor.products.index')" variant="outline">{{ __('View products') }}</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Orders containing your products')" class="[&_table]:min-w-[64rem]">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Order') }}</th>
                    <th scope="col">{{ __('Placed') }}</th>
                    <th scope="col" class="text-right">{{ __('Items') }}</th>
                    <th scope="col" class="text-right">{{ __('Your total') }}</th>
                    <th scope="col">{{ __('Order status') }}</th>
                    <th scope="col">{{ __('Payment') }}</th>
                    <th scope="col" class="text-right"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </x-slot:head>
            @foreach ($orders as $order)
                @php $vendorTotal = $order->items->sum(fn ($item) => (float) $item->line_total); @endphp
                <tr>
                    <td class="font-mono font-semibold text-slate-900">{{ $order->order_number }}</td>
                    <td class="whitespace-nowrap">
                        <time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('M j, Y') }}</time>
                    </td>
                    <td class="text-right tabular-nums">{{ number_format($order->items->sum('quantity')) }}</td>
                    <td class="whitespace-nowrap text-right font-semibold tabular-nums text-slate-900">৳ {{ number_format($vendorTotal, 2) }}</td>
                    <td><x-ui.status :status="$order->status" /></td>
                    <td><x-ui.status :status="$order->vendor_payment_status" /></td>
                    <td class="text-right">
                        <x-ui.button :href="route('vendor.orders.show', $order)" variant="outline">
                            {{ __('View and fulfill') }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="ui-pagination mt-6">{{ $orders->links() }}</div>
    @endif
@endsection
