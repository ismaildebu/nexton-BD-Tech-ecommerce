@extends('layouts.admin')
@section('title', __('Settlements'))
@section('content')
    <div class="ui-page-wide">
        <x-ui.page-header
            :title="__('Settlements')"
            :description="__('Settlements move from pending to processing, then to paid or failed.')"
            :eyebrow="__('Marketplace finance')"
        />

        <x-ui.card class="mb-6">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Create settlement') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Start a new payout for a marketplace vendor.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settlements.store') }}" class="grid items-end gap-5 md:grid-cols-12">
                @csrf
                <div class="md:col-span-6">
                    <x-form.select name="vendor_id" :label="__('Vendor')" required>
                        <option value="">{{ __('Select vendor') }}</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected((int) old('vendor_id') === $vendor->id)>{{ $vendor->business_name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="md:col-span-3">
                    <x-form.input name="amount" :label="__('Amount')" type="number" min="0.01" step="0.01" inputmode="decimal" required />
                </div>
                <div class="md:col-span-3">
                    <x-ui.button type="submit" variant="secondary" class="w-full">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Create settlement') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        @if ($settlements->count())
            <x-ui.table :caption="__('Vendor settlements and payout status')" class="[&_table]:min-w-[820px]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Vendor') }}</th>
                        <th scope="col" class="text-right">{{ __('Amount') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Created') }}</th>
                        <th scope="col" class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($settlements as $settlement)
                    <tr>
                        <td class="font-semibold text-slate-900">{{ $settlement->vendor->business_name ?? __('Deleted vendor') }}</td>
                        <td class="whitespace-nowrap text-right font-bold text-slate-900">৳ {{ number_format($settlement->amount, 2) }}</td>
                        <td><x-ui.status>{{ __(ucfirst($settlement->status)) }}</x-ui.status></td>
                        <td class="whitespace-nowrap text-xs text-slate-500">
                            <time datetime="{{ $settlement->created_at->toIso8601String() }}">{{ $settlement->created_at->format('d M Y') }}</time>
                        </td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-2">
                                @if ($settlement->status === 'pending')
                                    <form method="POST" action="{{ route('admin.settlements.transition', $settlement) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="processing">
                                        <x-ui.button type="submit" variant="secondary" size="sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87V14.13a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><circle cx="12" cy="12" r="9"/></svg>
                                            {{ __('Start processing') }}
                                        </x-ui.button>
                                    </form>
                                @elseif ($settlement->status === 'processing')
                                    <form method="POST" action="{{ route('admin.settlements.transition', $settlement) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="paid">
                                        <x-ui.button type="submit" variant="success" size="sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-9"/></svg>
                                            {{ __('Mark paid') }}
                                        </x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.settlements.transition', $settlement) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="failed">
                                        <x-ui.button type="submit" variant="outline" size="sm" class="border-rose-200 text-rose-600 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            {{ __('Mark failed') }}
                                        </x-ui.button>
                                    </form>
                                @else
                                    <span class="text-xs font-medium text-slate-400">{{ __('Final status') }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No settlements recorded')" :description="__('Create a settlement above to begin tracking vendor payouts.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($settlements->hasPages())
            <div class="ui-pagination mt-6">{{ $settlements->links() }}</div>
        @endif
    </div>
@endsection
