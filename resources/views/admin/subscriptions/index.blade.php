@extends('layouts.admin')
@section('title', __('Subscriptions'))
@section('content')
    <div class="ui-page-wide">
        <x-ui.page-header
            :title="__('Subscriptions')"
            :description="__('Create inactive plans, then activate or cancel them with valid date ranges.')"
            :eyebrow="__('Marketplace finance')"
        />

        <x-ui.card class="mb-6">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Create subscription') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Set up a vendor plan and an optional date range.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @csrf
                <x-form.select name="vendor_id" :label="__('Vendor')" required>
                    <option value="">{{ __('Select vendor') }}</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected((int) old('vendor_id') === $vendor->id)>{{ $vendor->business_name }}</option>
                    @endforeach
                </x-form.select>
                <x-form.input name="plan" :label="__('Plan')" maxlength="64" :placeholder="__('e.g. Premium')" />
                <x-form.input name="starts_at" :label="__('Starts at (optional)')" type="datetime-local" />
                <x-form.input name="expires_at" :label="__('Expires at (optional)')" type="datetime-local" />

                <div class="md:col-span-2 lg:col-span-3 flex items-center">
                    <p class="ui-help">{{ __('New subscriptions are inactive. Activation defaults the start date to now when omitted.') }}</p>
                </div>
                <x-ui.button type="submit" variant="primary" class="w-full lg:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Create subscription') }}
                </x-ui.button>
            </form>
        </x-ui.card>

        @if ($subscriptions->count())
            <x-ui.table :caption="__('Vendor subscriptions and plan status')" class="[&_table]:min-w-[980px]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Vendor') }}</th>
                        <th scope="col">{{ __('Plan') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Dates') }}</th>
                        <th scope="col" class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($subscriptions as $subscription)
                    <tr>
                        <td class="font-semibold text-slate-900">{{ $subscription->vendor->business_name ?? __('Deleted vendor') }}</td>
                        <td>{{ $subscription->plan ?: __('General') }}</td>
                        <td><x-ui.status>{{ __(ucfirst($subscription->status)) }}</x-ui.status></td>
                        <td class="text-xs leading-6 text-slate-500">
                            <span class="block"><span class="font-semibold text-slate-600">{{ __('Starts') }}:</span> {{ $subscription->starts_at?->format('d M Y, H:i') ?? '—' }}</span>
                            <span class="block"><span class="font-semibold text-slate-600">{{ __('Expires') }}:</span> {{ $subscription->expires_at?->format('d M Y, H:i') ?? __('No expiry') }}</span>
                        </td>
                        <td>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                @if ($subscription->status === 'inactive')
                                    @php
                                        $hasOldSubscription = (int) old('subscription_id') === $subscription->id;
                                    @endphp
                                    <form method="POST" action="{{ route('admin.subscriptions.transition', $subscription) }}" class="flex flex-wrap items-end gap-2">
                                        @csrf
                                        <input type="hidden" name="status" value="active">
                                        <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">
                                        <div>
                                            <label for="subscription-start-{{ $subscription->id }}" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ __('Start') }}</label>
                                            <input id="subscription-start-{{ $subscription->id }}" name="starts_at" type="datetime-local" value="{{ $hasOldSubscription ? old('starts_at', $subscription->starts_at?->format('Y-m-d\TH:i')) : $subscription->starts_at?->format('Y-m-d\TH:i') }}" class="min-w-40 text-xs" @if ($hasOldSubscription && $errors->has('starts_at')) aria-invalid="true" @endif>
                                            @if ($hasOldSubscription && $errors->has('starts_at'))
                                                <p class="ui-error text-xs">{{ $errors->first('starts_at') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <label for="subscription-expiry-{{ $subscription->id }}" class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ __('Expiry') }}</label>
                                            <input id="subscription-expiry-{{ $subscription->id }}" name="expires_at" type="datetime-local" value="{{ $hasOldSubscription ? old('expires_at', $subscription->expires_at?->format('Y-m-d\TH:i')) : $subscription->expires_at?->format('Y-m-d\TH:i') }}" class="min-w-40 text-xs" @if ($hasOldSubscription && $errors->has('expires_at')) aria-invalid="true" @endif>
                                            @if ($hasOldSubscription && $errors->has('expires_at'))
                                                <p class="ui-error text-xs">{{ $errors->first('expires_at') }}</p>
                                            @endif
                                        </div>
                                        <x-ui.button type="submit" variant="success" size="sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-9"/></svg>
                                            {{ __('Activate') }}
                                        </x-ui.button>
                                    </form>
                                @elseif ($subscription->status === 'active')
                                    <form method="POST" action="{{ route('admin.subscriptions.transition', $subscription) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="cancelled">
                                        <x-ui.button type="submit" variant="outline" size="sm" class="border-rose-200 text-rose-600 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            {{ __('Cancel') }}
                                        </x-ui.button>
                                    </form>
                                @else
                                    <span class="text-xs font-medium text-slate-400">{{ __('Cancelled') }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No subscriptions recorded')" :description="__('Create a subscription above to start managing vendor plans.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($subscriptions->hasPages())
            <div class="ui-pagination mt-6">{{ $subscriptions->links() }}</div>
        @endif
    </div>
@endsection
