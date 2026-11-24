@extends('layouts.vendor')
@section('title', __('Settlements'))
@section('content')
    @php
        $totalAmount = (float) $settlements->sum(fn ($settlement) => (float) $settlement->amount);
        $paidAmount = (float) $settlements->where('status', 'paid')->sum(fn ($settlement) => (float) $settlement->amount);
        $inProgressCount = $settlements->whereIn('status', ['pending', 'processing'])->count();
    @endphp

    <x-ui.page-header
        :title="__('Settlements')"
        :description="__('Track settlement amounts and payment progress for your vendor account.')"
        :eyebrow="__('Vendor finances')"
    />

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Total on this page') }}</p>
            <p class="ui-stat-value tabular-nums">৳ {{ number_format($totalAmount, 2) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('All settlement amounts shown') }}</p>
        </x-ui.card>
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Paid on this page') }}</p>
            <p class="ui-stat-value tabular-nums text-emerald-700">৳ {{ number_format($paidAmount, 2) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Completed settlement payments') }}</p>
        </x-ui.card>
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Pending or processing') }}</p>
            <p class="ui-stat-value tabular-nums text-amber-700">{{ number_format($inProgressCount) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Records still in progress') }}</p>
        </x-ui.card>
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Entries on this page') }}</p>
            <p class="ui-stat-value tabular-nums text-accent-700">{{ number_format($settlements->count()) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Settlement records shown') }}</p>
        </x-ui.card>
    </div>

    @if ($settlements->isEmpty())
        <x-ui.card :padding="false" class="mt-6">
            <x-ui.empty-state
                :title="__('No settlements yet.')"
                :description="__('Settlement records and their payment status will appear here when available.')"
            />
        </x-ui.card>
    @else
        <x-ui.card :padding="false" class="mt-6">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Settlement records') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Summary values cover the current page only.') }}</p>
            </div>
            <x-ui.table :caption="__('Settlement records')" class="[&_table]:min-w-[48rem]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Reference') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Created') }}</th>
                        <th scope="col">{{ __('Last updated') }}</th>
                        <th scope="col" class="text-right">{{ __('Amount') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($settlements as $settlement)
                    <tr>
                        <td class="font-mono text-sm font-semibold text-slate-800">#{{ $settlement->id }}</td>
                        <td><x-ui.status :status="$settlement->status" /></td>
                        <td class="whitespace-nowrap text-slate-600">{{ $settlement->created_at?->format('M j, Y') ?? __('Not available') }}</td>
                        <td class="whitespace-nowrap text-slate-600">{{ $settlement->updated_at?->format('M j, Y') ?? __('Not available') }}</td>
                        <td class="text-right font-bold tabular-nums text-slate-900">৳ {{ number_format((float) $settlement->amount, 2) }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-slate-100 p-5 sm:px-6">{{ $settlements->links() }}</div>
        </x-ui.card>
    @endif
@endsection
