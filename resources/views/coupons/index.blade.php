@extends('layouts.admin')
@section('title', __('Coupons'))
@section('content')
    <x-ui.page-header
        :title="__('Coupons')"
        :eyebrow="__('Discount management')"
        :description="__('Create fixed or percentage discounts and manage their validity window.')"
    />

    <x-ui.card class="mb-7">
        <div class="mb-5 flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </span>
            <div>
                <h2 class="ui-section-title">{{ __('Create coupon') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Set the discount rules and optional start and expiry dates.') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.coupons.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @csrf

            <x-form.input
                name="code"
                :label="__('Code')"
                required
                maxlength="64"
                autocomplete="off"
                class="uppercase"
                :hint="__('Use a unique code that customers can enter at checkout.')"
            />

            <x-form.select name="type" :label="__('Type')" required>
                <option value="fixed" @selected(old('type', 'fixed') === 'fixed')>{{ __('Fixed amount') }}</option>
                <option value="percent" @selected(old('type') === 'percent')>{{ __('Percentage') }}</option>
            </x-form.select>

            <x-form.input
                name="value"
                :label="__('Discount value')"
                type="number"
                required
                min="0.01"
                step="0.01"
                :hint="__('Use 1–100 for a percentage, or a positive amount for a fixed discount.')"
            />

            <x-form.input
                name="minimum_order"
                :label="__('Minimum order')"
                type="number"
                :value="'0.00'"
                required
                min="0"
                step="0.01"
                :hint="__('Set the minimum cart total required to use this coupon.')"
            />

            <x-form.input
                name="starts_at"
                :label="__('Starts at')"
                type="datetime-local"
                :hint="__('Leave blank to start immediately.')"
            />

            <x-form.input
                name="expires_at"
                :label="__('Expires at')"
                type="datetime-local"
                :hint="__('Leave blank for no expiry date.')"
            />

            <div class="md:col-span-2">
                <input type="hidden" name="is_active" value="0">
                <x-form.checkbox
                    name="is_active"
                    :label="__('Active')"
                    :checked="true"
                    :hint="__('Customers can use this coupon while it is active.')"
                />
            </div>

            <div class="md:col-span-2">
                <x-ui.button type="submit" variant="primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                    {{ __('Create coupon') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($coupons->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                :title="__('No coupons yet')"
                :description="__('Create your first coupon above to offer a fixed or percentage discount.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </x-slot:icon>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Created coupons and validity details')">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Code') }}</th>
                    <th scope="col">{{ __('Discount') }}</th>
                    <th scope="col">{{ __('Minimum order') }}</th>
                    <th scope="col">{{ __('Validity') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                </tr>
            </x-slot:head>
            @foreach ($coupons as $c)
                <tr>
                    <td class="min-w-40 font-mono font-bold text-slate-900">{{ $c->code }}</td>
                    <td class="whitespace-nowrap font-semibold text-slate-800">
                        {{ $c->type === 'percent' ? number_format((float) $c->value, 2).'%' : '৳ '.number_format((float) $c->value, 2) }}
                    </td>
                    <td class="whitespace-nowrap">৳ {{ number_format((float) $c->minimum_order, 2) }}</td>
                    <td class="min-w-56 text-xs leading-5">
                        <span class="block">
                            <span class="text-slate-400">{{ __('Starts') }}:</span>
                            @if ($c->starts_at)
                                <time datetime="{{ $c->starts_at->toIso8601String() }}">{{ $c->starts_at->format('d M Y H:i') }}</time>
                            @else
                                {{ __('No start date') }}
                            @endif
                        </span>
                        <span class="mt-1 block">
                            <span class="text-slate-400">{{ __('Expires') }}:</span>
                            @if ($c->expires_at)
                                <time datetime="{{ $c->expires_at->toIso8601String() }}">{{ $c->expires_at->format('d M Y H:i') }}</time>
                            @else
                                {{ __('No expiry') }}
                            @endif
                        </span>
                    </td>
                    <td>
                        <x-ui.status>
                            {{ $c->is_active ? __('Active') : __('Inactive') }}
                        </x-ui.status>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="ui-pagination mt-5">{{ $coupons->links() }}</div>
    @endif
@endsection