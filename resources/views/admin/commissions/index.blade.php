@extends('layouts.admin')

@section('title', __('Commissions'))

@section('content')
    <div class="ui-page-wide">
        <x-ui.page-header
            :title="__('Commissions')"
            :description="__('Record an approved manual commission or derive one from a vendor order and rate.')"
            :eyebrow="__('Marketplace finance')"
        />

        <x-ui.card class="mb-6">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Record commission') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Choose a vendor, then enter an amount or a percentage rate.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('commissions.store') }}" class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @csrf
                <div class="lg:col-span-2">
                    <x-form.select name="vendor_id" :label="__('Vendor')" required>
                        <option value="">{{ __('Select vendor') }}</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected((int) old('vendor_id') === $vendor->id)>{{ $vendor->business_name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="lg:col-span-2">
                    <x-form.select name="order_id" :label="__('Order (optional)')" :hint="__('Select an order to calculate the commission from this vendor’s items.')">
                        <option value="">{{ __('Manual commission') }}</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" @selected((int) old('order_id') === $order->id)>{{ $order->order_number }} — ৳ {{ number_format($order->total, 2) }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <x-form.input name="amount" :label="__('Amount (optional with rate)')" type="number" min="0.01" step="0.01" inputmode="decimal" />
                <x-form.input name="rate" :label="__('Rate % (optional)')" type="number" min="0" max="100" step="0.01" inputmode="decimal" />

                <div class="md:col-span-2 lg:col-span-3 flex items-center">
                    <p class="ui-help">
                        <svg class="mr-1 inline h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.122a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('Linked amounts are calculated from the vendor items in the selected order. A supplied amount must match the rate or stay within the vendor order total.') }}
                    </p>
                </div>
                <x-ui.button type="submit" variant="primary" class="w-full lg:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Record commission') }}
                </x-ui.button>
            </form>
        </x-ui.card>

        @if ($commissions->count())
            <x-ui.table :caption="__('Recorded commissions')">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Vendor') }}</th>
                        <th scope="col">{{ __('Order') }}</th>
                        <th scope="col" class="text-right">{{ __('Amount') }}</th>
                        <th scope="col" class="text-right">{{ __('Rate') }}</th>
                        <th scope="col">{{ __('Recorded') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($commissions as $commission)
                    <tr>
                        <td class="font-semibold text-slate-900">{{ $commission->vendor->business_name ?? __('Deleted vendor') }}</td>
                        <td>
                            @if ($commission->order)
                                <a href="{{ route('admin.orders.show', $commission->order) }}" class="inline-flex min-h-9 items-center font-mono text-xs font-bold text-brand-600 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">{{ $commission->order->order_number }}</a>
                            @else
                                <x-ui.badge tone="neutral">{{ __('Manual') }}</x-ui.badge>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-right font-bold text-slate-900">৳ {{ number_format($commission->amount, 2) }}</td>
                        <td class="whitespace-nowrap text-right">{{ number_format($commission->rate, 2) }}%</td>
                        <td class="whitespace-nowrap text-xs text-slate-500">
                            <time datetime="{{ $commission->created_at->toIso8601String() }}">{{ $commission->created_at->format('d M Y, H:i') }}</time>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No commissions recorded')" :description="__('Use the form above to record the first manual or order-linked commission.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($commissions->hasPages())
            <div class="ui-pagination mt-6">{{ $commissions->links() }}</div>
        @endif
    </div>
@endsection
