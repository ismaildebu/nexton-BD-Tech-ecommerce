@extends('layouts.vendor')
@section('title', __('Commissions'))
@section('content')
    @php
        $totalAmount = (float) $commissions->sum(fn ($commission) => (float) $commission->amount);
        $averageRate = $commissions->avg(fn ($commission) => (float) $commission->rate);
    @endphp

    <x-ui.page-header
        :title="__('Commissions')"
        :description="__('Review commission amounts and rates recorded for your vendor account.')"
        :eyebrow="__('Vendor finances')"
    />

    <div class="grid gap-5 sm:grid-cols-3">
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Total on this page') }}</p>
            <p class="ui-stat-value tabular-nums">৳ {{ number_format($totalAmount, 2) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Commission amount shown below') }}</p>
        </x-ui.card>
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Entries on this page') }}</p>
            <p class="ui-stat-value tabular-nums text-accent-700">{{ number_format($commissions->count()) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Recorded commission entries') }}</p>
        </x-ui.card>
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Average rate') }}</p>
            <p class="ui-stat-value tabular-nums text-brand-600">{{ number_format((float) $averageRate, 2) }}%</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Across the entries shown') }}</p>
        </x-ui.card>
    </div>

    @if ($commissions->isEmpty())
        <x-ui.card :padding="false" class="mt-6">
            <x-ui.empty-state
                :title="__('No commissions yet.')"
                :description="__('Commission records will appear here when they are associated with your vendor account.')"
            />
        </x-ui.card>
    @else
        <x-ui.card :padding="false" class="mt-6">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Commission records') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Summary values cover the current page only.') }}</p>
            </div>
            <x-ui.table :caption="__('Commission records')" class="[&_table]:min-w-[48rem]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Date') }}</th>
                        <th scope="col">{{ __('Order reference') }}</th>
                        <th scope="col" class="text-right">{{ __('Rate') }}</th>
                        <th scope="col" class="text-right">{{ __('Amount') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($commissions as $commission)
                    <tr>
                        <td class="whitespace-nowrap text-slate-600">{{ $commission->created_at?->format('M j, Y') ?? __('Not available') }}</td>
                        <td class="font-mono text-sm text-slate-700">
                            {{ $commission->order_id ? '#'.$commission->order_id : __('Not linked') }}
                        </td>
                        <td class="text-right tabular-nums text-slate-600">{{ number_format((float) $commission->rate, 2) }}%</td>
                        <td class="text-right font-bold tabular-nums text-slate-900">৳ {{ number_format((float) $commission->amount, 2) }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-slate-100 p-5 sm:px-6">{{ $commissions->links() }}</div>
        </x-ui.card>
    @endif
@endsection
