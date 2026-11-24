@extends('layouts.vendor')
@section('title', __('Subscriptions'))
@section('content')
    @php
        $activeCount = $subscriptions->where('status', 'active')->count();
        $inactiveCount = $subscriptions->where('status', 'inactive')->count();
    @endphp

    <x-ui.page-header
        :title="__('Subscriptions')"
        :description="__('Review your vendor plan, subscription status, and billing dates.')"
        :eyebrow="__('Vendor finances')"
    />

    <div class="grid gap-5 sm:grid-cols-3">
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Active on this page') }}</p>
            <p class="ui-stat-value tabular-nums text-emerald-700">{{ number_format($activeCount) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Currently active subscriptions') }}</p>
        </x-ui.card>
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Inactive on this page') }}</p>
            <p class="ui-stat-value tabular-nums text-accent-700">{{ number_format($inactiveCount) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Subscriptions not currently active') }}</p>
        </x-ui.card>
        <x-ui.card class="ui-stat">
            <p class="ui-stat-label">{{ __('Entries on this page') }}</p>
            <p class="ui-stat-value tabular-nums text-brand-600">{{ number_format($subscriptions->count()) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ __('Subscription records shown') }}</p>
        </x-ui.card>
    </div>

    @if ($subscriptions->isEmpty())
        <x-ui.card :padding="false" class="mt-6">
            <x-ui.empty-state
                :title="__('No subscriptions yet.')"
                :description="__('Your vendor subscription records will appear here when they are available.')"
            />
        </x-ui.card>
    @else
        <x-ui.card :padding="false" class="mt-6">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Subscription records') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Summary values cover the current page only.') }}</p>
            </div>
            <x-ui.table :caption="__('Subscription records')" class="[&_table]:min-w-[52rem]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Plan') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Starts') }}</th>
                        <th scope="col">{{ __('Expires') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($subscriptions as $subscription)
                    <tr>
                        <td class="font-semibold text-slate-900">{{ $subscription->plan ?: __('No plan selected') }}</td>
                        <td><x-ui.status :status="$subscription->status" /></td>
                        <td class="whitespace-nowrap text-slate-600">{{ $subscription->starts_at?->format('M j, Y') ?? __('Not set') }}</td>
                        <td class="whitespace-nowrap text-slate-600">{{ $subscription->expires_at?->format('M j, Y') ?? __('Not set') }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-slate-100 p-5 sm:px-6">{{ $subscriptions->links() }}</div>
        </x-ui.card>
    @endif
@endsection
