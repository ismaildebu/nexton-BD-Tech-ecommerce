@extends('layouts.frontend')

@section('title', __('Payments'))

@section('content')
    <div class="ui-page">
        <x-ui.page-header :title="__('Payment history')" :description="__('Review payment amounts, gateways and the latest status for each transaction.')"
            :eyebrow="__('Shopping activity')">
            <x-slot:action><x-ui.button :href="route('orders.index')" variant="outline">{{ __('View orders') }}</x-ui.button></x-slot:action>
        </x-ui.page-header>

        @if ($payments->isEmpty())
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No payments yet')" :description="__('Your payment records will appear here after you place an order.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18v13H3V6zm0 4h18M6 15h3"/></svg>
                    </x-slot:icon>
                    <x-slot:action><x-ui.button :href="route('orders.index')">{{ __('View your orders') }}</x-ui.button></x-slot:action>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <div class="hidden md:block">
                <x-ui.table :caption="__('Payment history')" class="[&_table]:min-w-[850px]">
                    <x-slot:head>
                        <tr>
                            <th scope="col">{{ __('Date') }}</th>
                            <th scope="col">{{ __('Order') }}</th>
                            <th scope="col">{{ __('Gateway') }}</th>
                            <th scope="col">{{ __('Transaction') }}</th>
                            <th scope="col">{{ __('Amount') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('Actions') }}</span></th>
                        </tr>
                    </x-slot:head>
                    @foreach ($payments as $payment)
                        <tr>
                            <td class="whitespace-nowrap">{{ $payment->created_at->translatedFormat('d M Y') }}</td>
                            <td><a href="{{ route('orders.show', $payment->order_id) }}" class="font-bold text-slate-900 hover:text-brand-600">{{ $payment->order?->order_number ?? __('Order #:id', ['id' => $payment->order_id]) }}</a></td>
                            <td class="capitalize">{{ $payment->gateway ?: '—' }}</td>
                            <td class="max-w-[12rem] truncate" title="{{ $payment->transaction_id ?: '—' }}">{{ $payment->transaction_id ?: '—' }}</td>
                            <td class="whitespace-nowrap font-bold text-slate-900">৳ {{ number_format($payment->amount, 2) }}</td>
                            <td><x-ui.status :status="$payment->status" /></td>
                            <td class="text-right"><x-ui.button :href="route('orders.show', $payment->order_id)" variant="outline" size="sm">{{ __('View order') }}</x-ui.button></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>

            <div class="space-y-4 md:hidden">
                @foreach ($payments as $payment)
                    <x-ui.card>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a href="{{ route('orders.show', $payment->order_id) }}" class="text-sm font-extrabold text-slate-950 hover:text-brand-600">{{ $payment->order?->order_number ?? __('Order #:id', ['id' => $payment->order_id]) }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $payment->created_at->translatedFormat('d M Y, h:i A') }}</p>
                            </div>
                            <x-ui.status :status="$payment->status" />
                        </div>
                        <div class="mt-4 flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $payment->gateway ?: '—' }}</p>
                                @if ($payment->transaction_id)<p class="mt-1 truncate text-xs text-slate-500">{{ $payment->transaction_id }}</p>@endif
                            </div>
                            <p class="shrink-0 text-lg font-extrabold text-slate-950">৳ {{ number_format($payment->amount, 2) }}</p>
                        </div>
                        <x-ui.button :href="route('orders.show', $payment->order_id)" variant="outline" size="sm" class="mt-4 w-full">{{ __('View order') }}</x-ui.button>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="ui-pagination mt-6">{{ $payments->links() }}</div>
        @endif
    </div>
@endsection
