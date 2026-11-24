@extends('layouts.vendor')
@section('title', __('Bank Accounts'))
@section('content')
    <x-ui.page-header
        :title="__('Bank Accounts')"
        :description="__('Add payout bank accounts and monitor their verification status.')"
        :eyebrow="__('Finance')"
    />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <x-ui.card>
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Add bank account') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Enter the account details used for vendor payouts.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('vendor.bank-accounts.store', $vendor) }}" class="mt-6 space-y-6">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="account_holder_name" :label="__('Account holder name')" maxlength="150" required />
                    <x-form.input name="bank_name" :label="__('Bank name')" maxlength="150" required />
                    <x-form.input name="branch_name" :label="__('Branch name')" maxlength="150" />
                    <x-form.input
                        name="account_number"
                        :label="__('Account number')"
                        maxlength="32"
                        minlength="6"
                        autocomplete="off"
                        :hint="__('Only the last four digits are displayed after saving.')"
                        required
                    />
                    <x-form.input name="routing_number" :label="__('Routing number')" maxlength="64" />
                    <x-form.select name="account_type" :label="__('Account type')">
                        <option value="">{{ __('— Select —') }}</option>
                        @foreach (['savings' => __('Savings'), 'current' => __('Current'), 'checking' => __('Checking')] as $value => $label)
                            <option value="{{ $value }}" @selected(old('account_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <x-form.checkbox
                    name="is_default"
                    :label="__('Set as default account')"
                    :hint="__('Choose whether this should be your default payout account.')"
                />
                <div class="border-t border-slate-100 pt-6">
                    <x-ui.button type="submit" variant="primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add account') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4v5c0 4.5-3 7.5-8 9-5-1.5-8-4.5-8-9V7l8-4zm-3 9 2 2 4-4"/></svg>
            </span>
            <h2 class="mt-4 ui-section-title">{{ __('Secure account details') }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Account numbers are encrypted. Saved accounts only display a masked number for security.') }}</p>
            <div class="mt-5 rounded-xl bg-slate-50 p-4 text-xs leading-5 text-slate-500">
                {{ __('Setting a new default account replaces the previous default after it is added.') }}
            </div>
        </x-ui.card>
    </div>

    <x-ui.card :padding="false" class="mt-8">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6">
            <h2 class="ui-section-title">{{ __('Saved bank accounts') }}</h2>
            <span class="text-sm font-semibold tabular-nums text-slate-500">{{ number_format($accounts->count()) }}</span>
        </div>
        @if ($accounts->isEmpty())
            <x-ui.empty-state
                :title="__('No bank accounts saved.')"
                :description="__('Add a bank account above to prepare for vendor payouts.')"
            />
        @else
            <x-ui.table :caption="__('Saved vendor bank accounts')" class="rounded-none border-0 shadow-none [&_table]:min-w-[44rem]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Bank') }}</th>
                        <th scope="col">{{ __('Holder') }}</th>
                        <th scope="col">{{ __('Account') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($accounts as $account)
                    <tr>
                        <td>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-slate-900">{{ $account->bank_name }}</span>
                                @if ($account->is_default)
                                    <x-ui.badge tone="accent">{{ __('Default') }}</x-ui.badge>
                                @endif
                            </div>
                            @if ($account->branch_name)
                                <p class="mt-1 text-xs text-slate-500">{{ $account->branch_name }}</p>
                            @endif
                        </td>
                        <td>{{ $account->account_holder_name }}</td>
                        <td>
                            <span class="font-mono font-semibold text-slate-900">{{ $account->masked_account_number }}</span>
                            @if ($account->account_type)
                                <p class="mt-1 text-xs text-slate-500">{{ ucfirst($account->account_type) }}</p>
                            @endif
                        </td>
                        <td>
                            <x-ui.badge :tone="match ($account->verification_status ?: 'pending') { 'verified', 'approved' => 'success', 'rejected' => 'danger', default => 'warning' }">
                                {{ ucfirst($account->verification_status ?: 'pending') }}
                            </x-ui.badge>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>
@endsection
