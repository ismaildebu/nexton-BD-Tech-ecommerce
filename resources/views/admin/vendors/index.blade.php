@extends('layouts.admin')

@section('title', __('Vendors'))

@section('content')
    <div class="ui-page">
        <x-ui.page-header
            :title="__('Vendors')"
            :description="__('Review marketplace partners, verification progress, and account standing.')"
            :eyebrow="__('Marketplace')"
        />

        @if ($vendors->count())
            <x-ui.table :caption="__('Vendor verification and account status')">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Vendor') }}</th>
                        <th scope="col">{{ __('Verification') }}</th>
                        <th scope="col">{{ __('Account') }}</th>
                        <th scope="col">{{ __('Registered') }}</th>
                        <th scope="col" class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($vendors as $vendor)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-sm font-bold text-accent-700" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($vendor->business_name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.vendors.show', $vendor) }}" class="font-bold text-slate-900 hover:text-brand-600 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                                        {{ $vendor->business_name }}
                                    </a>
                                    <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $vendor->vendor_code }}</p>
                                </div>
                            </div>
                            @if ($vendor->verification_status?->value === 'rejected' && $vendor->rejection_reason)
                                <p class="mt-2 max-w-md text-xs leading-5 text-rose-600">{{ $vendor->rejection_reason }}</p>
                            @endif
                        </td>
                        <td><x-ui.status>{{ $vendor->verification_status->label() }}</x-ui.status></td>
                        <td>
                            @if ($vendor->status->value === 'blocked')
                                <x-ui.badge tone="danger">{{ $vendor->status->label() }}</x-ui.badge>
                            @else
                                <x-ui.status>{{ $vendor->status->label() }}</x-ui.status>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-500">
                            <time datetime="{{ $vendor->created_at->toIso8601String() }}">{{ $vendor->created_at->format('d M Y') }}</time>
                        </td>
                        <td class="text-right">
                            <x-ui.button :href="route('admin.vendors.show', $vendor)" variant="outline" size="sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ __('View details') }}
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state
                    :title="__('No vendors yet')"
                    :description="__('Marketplace partners will appear here when they register.')"
                >
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($vendors->hasPages())
            <div class="ui-pagination mt-6">{{ $vendors->links() }}</div>
        @endif
    </div>
@endsection
