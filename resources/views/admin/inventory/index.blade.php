@extends('layouts.admin')
@section('title', __('Inventory'))
@section('content')
    <x-ui.page-header
        :title="__('Inventory')"
        :eyebrow="__('Stock operations')"
        :description="__('Adjust stock levels and review a complete audit trail of inventory movements.')"
    />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-1">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Adjust stock') }}</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Use a positive quantity to receive stock or a negative quantity to remove it. Every adjustment is recorded.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-4">
                @csrf
                <x-form.select name="product_id" :label="__('Product')" required>
                    <option value="">{{ __('Select a product') }}</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>
                            {{ $product->name }} ({{ __('Stock') }}: {{ $product->inventory?->quantity ?? 0 }})
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.input
                    name="quantity"
                    :label="__('Quantity adjustment')"
                    type="number"
                    min="-1000000"
                    max="1000000"
                    step="1"
                    required
                    :placeholder="__('e.g. 10 or -3')"
                    :hint="__('Enter a non-zero whole number.')"
                />

                <x-form.input
                    name="low_stock_threshold"
                    :label="__('Low-stock threshold (optional)')"
                    type="number"
                    min="0"
                    max="1000000"
                    step="1"
                    :hint="__('Leave blank to keep the current threshold.')"
                />

                <x-form.textarea
                    name="note"
                    :label="__('Audit note')"
                    rows="3"
                    maxlength="255"
                    :hint="__('Add context for this stock movement (up to 255 characters).')"
                ></x-form.textarea>

                <x-ui.button type="submit" variant="primary" class="w-full">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Record adjustment') }}
                </x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card :padding="false" class="min-w-0 xl:col-span-2">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Stock levels') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Current inventory across all products.') }}</p>
            </div>

            @if ($products->isEmpty())
                <x-ui.empty-state
                    :title="__('No products found')"
                    :description="__('Stock levels will appear here once products are added to the catalog.')"
                >
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 7l2-4h12l2 4M5 7v14h14V7M9 11h6"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            @else
                <x-ui.table :caption="__('Current product stock levels')" class="rounded-none border-0 shadow-none">
                    <x-slot:head>
                        <tr>
                            <th scope="col">{{ __('Product') }}</th>
                            <th scope="col">{{ __('Quantity') }}</th>
                            <th scope="col">{{ __('Low-stock threshold') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($products as $product)
                        @php
                            $quantity = $product->inventory?->quantity ?? 0;
                            $threshold = $product->inventory?->low_stock_threshold ?? 5;
                        @endphp
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $product->name }}</td>
                            <td class="whitespace-nowrap font-bold text-slate-800">{{ number_format($quantity) }}</td>
                            <td class="whitespace-nowrap">{{ number_format($threshold) }}</td>
                            <td>
                                <x-ui.badge :tone="$quantity === 0 ? 'danger' : ($quantity <= $threshold ? 'warning' : 'success')">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                    {{ $quantity === 0 ? __('Out of stock') : ($quantity <= $threshold ? __('Low stock') : __('In stock')) }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
                <div class="ui-pagination border-t border-slate-100 p-4">{{ $products->links() }}</div>
            @endif
        </x-ui.card>
    </div>

    <x-ui.card :padding="false" class="mt-6">
        <div class="border-b border-slate-100 p-5 sm:p-6">
            <h2 class="ui-section-title">{{ __('Recent inventory movements') }}</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Audit trail for manual and order-related stock changes.') }}</p>
        </div>

        @if ($movements->isEmpty())
            <x-ui.empty-state
                :title="__('No inventory movements recorded')"
                :description="__('Manual adjustments and order-related stock changes will be listed here.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-ui.empty-state>
        @else
            <x-ui.table :caption="__('Recent inventory movement audit trail')" class="rounded-none border-0 shadow-none">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Date') }}</th>
                        <th scope="col">{{ __('Product') }}</th>
                        <th scope="col">{{ __('Type') }}</th>
                        <th scope="col">{{ __('Quantity') }}</th>
                        <th scope="col">{{ __('Actor / note') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($movements as $movement)
                    <tr>
                        <td class="whitespace-nowrap text-xs">
                            <time datetime="{{ $movement->created_at?->toIso8601String() }}">{{ $movement->created_at?->format('d M Y H:i') }}</time>
                        </td>
                        <td class="font-semibold text-slate-900">{{ $movement->product->name ?? __('Deleted product') }}</td>
                        <td>
                            <x-ui.badge tone="neutral">{{ str($movement->type)->headline() }}</x-ui.badge>
                        </td>
                        <td class="whitespace-nowrap font-bold {{ $movement->quantity > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}
                        </td>
                        <td>
                            <span class="block font-medium text-slate-700">{{ $movement->user->name ?? __('System / order') }}</span>
                            @if ($movement->note)
                                <span class="mt-1 block text-xs text-slate-500">{{ $movement->note }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>
@endsection
