@extends('layouts.admin')
@section('title', __('Payments'))
@section('content')
    <x-ui.page-header
        :title="__('Payments')"
        :eyebrow="__('Payment operations')"
        :description="__('Confirm COD collection or record a refund manually.')"
    />

    @if ($payments->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                :title="__('No payments found')"
                :description="__('Payment records will appear here after customers begin placing orders.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </x-slot:icon>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Payment records and available actions')">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Order') }}</th>
                    <th scope="col">{{ __('Vendor') }}</th>
                    <th scope="col">{{ __('Gateway') }}</th>
                    <th scope="col">{{ __('Amount') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col">{{ __('Transaction ID') }}</th>
                    <th scope="col" class="text-right">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            @foreach ($payments as $payment)
                <tr class="align-top">
                    <td class="min-w-44">
                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="ui-link font-mono text-xs">{{ $payment->order->order_number }}</a>
                        <span class="mt-2 flex flex-wrap items-center gap-1.5">
                            <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $payment->order->status))) }}</x-ui.status>
                            <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $payment->order->payment_status))) }}</x-ui.status>
                        </span>
                    </td>
                    <td>{{ $payment->vendor?->business_name ?: '—' }}</td>
                    <td class="whitespace-nowrap font-semibold uppercase text-slate-700">{{ $payment->gateway ?: '—' }}</td>
                    <td class="whitespace-nowrap font-semibold text-slate-900">৳ {{ number_format($payment->amount, 2) }}</td>
                    <td>
                        <x-ui.status>{{ __(ucfirst(str_replace('_', ' ', $payment->status))) }}</x-ui.status>
                    </td>
                    <td class="max-w-40 break-all text-xs">{{ $payment->transaction_id ?: '—' }}</td>
                    <td class="min-w-56 text-right">
                        @if ($payment->status === 'pending')
                            @php
                                $hasPaymentError = (int) old('payment_id') === $payment->id && $errors->has('transaction_id');
                            @endphp
                            <form method="POST" action="{{ route('admin.payments.transition', $payment) }}" class="flex flex-wrap items-end justify-end gap-2">
                                @csrf
                                <input type="hidden" name="status" value="paid">
                                <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                <div class="min-w-40 flex-1 text-left">
                                    <label for="transaction_id-{{ $payment->id }}" class="ui-label text-xs">{{ __('Optional transaction ID') }}</label>
                                    <input
                                        id="transaction_id-{{ $payment->id }}"
                                        type="text"
                                        name="transaction_id"
                                        value="{{ $hasPaymentError ? old('transaction_id') : '' }}"
                                        maxlength="128"
                                        placeholder="{{ __('Optional transaction ID') }}"
                                        @if ($hasPaymentError) aria-invalid="true" aria-describedby="transaction_id-{{ $payment->id }}-error" @endif
                                    >
                                    @if ($hasPaymentError)
                                        <p id="transaction_id-{{ $payment->id }}-error" class="ui-error text-xs">{{ $errors->first('transaction_id') }}</p>
                                    @endif
                                </div>
                                <x-ui.button type="submit" variant="success" size="sm" class="min-h-10">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Mark paid') }}
                                </x-ui.button>
                            </form>
                        @elseif ($payment->status === 'paid')
                            <form method="POST" action="{{ route('admin.payments.transition', $payment) }}" class="text-right">
                                @csrf
                                <input type="hidden" name="status" value="refunded">
                                <x-ui.button type="submit" variant="outline" size="sm" class="min-h-10 text-rose-700 hover:border-rose-200 hover:bg-rose-50">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    {{ __('Refund') }}
                                </x-ui.button>
                            </form>
                        @else
                            <span class="text-xs text-slate-400">{{ __('No action available') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="ui-pagination mt-5">{{ $payments->links() }}</div>
    @endif
@endsection