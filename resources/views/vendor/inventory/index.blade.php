@extends('layouts.vendor')
@section('title', __('Inventory'))
@section('content')
    <x-ui.page-header
        :title="__('Inventory')"
        :description="__('Monitor stock levels, adjust quantities, and review recent inventory movements.')"
        :eyebrow="__('Operations')"
    />

    @if ($products->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state
                :title="__('No products available for inventory management.')"
                :description="__('Add a product to begin tracking and adjusting its stock.')"
            >
                <x-slot:action>
                    <x-ui.button :href="route('vendor.products.create')" variant="primary">{{ __('Add product') }}</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Product stock levels and inventory adjustment forms')" class="[&_table]:min-w-[68rem]">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Product') }}</th>
                    <th scope="col">{{ __('On hand') }}</th>
                    <th scope="col">{{ __('Low stock at') }}</th>
                    <th scope="col">{{ __('Stock status') }}</th>
                    <th scope="col">{{ __('Adjust stock') }}</th>
                </tr>
            </x-slot:head>
            @foreach ($products as $product)
                @php
                    $quantity = $product->inventory?->quantity ?? 0;
                    $threshold = $product->inventory?->low_stock_threshold ?? 5;
                    $stockTone = $quantity === 0 ? 'danger' : ($quantity <= $threshold ? 'warning' : 'success');
                    $stockLabel = $quantity === 0 ? __('Out of stock') : ($quantity <= $threshold ? __('Low stock') : __('In stock'));
                    $hasOldAdjustment = (string) old('product_id') === (string) $product->id;
                @endphp
                <tr>
                    <td class="min-w-48 font-semibold text-slate-900">{{ $product->name }}</td>
                    <td class="tabular-nums">{{ number_format($quantity) }}</td>
                    <td class="tabular-nums">{{ number_format($threshold) }}</td>
                    <td><x-ui.badge :tone="$stockTone">{{ $stockLabel }}</x-ui.badge></td>
                    <td class="min-w-[34rem]">
                        <form method="POST" action="{{ route('vendor.inventory.adjust') }}" class="grid grid-cols-2 items-end gap-3 lg:grid-cols-[7rem_7rem_12rem_auto]">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div>
                                <label for="direction-{{ $product->id }}" class="ui-label">{{ __('Direction') }} <span class="text-brand-600" aria-hidden="true">*</span></label>
                                <select id="direction-{{ $product->id }}" name="direction" required class="ui-input" @if ($hasOldAdjustment && $errors->has('direction')) aria-invalid="true" @endif>
                                    <option value="in" @selected(! $hasOldAdjustment || old('direction', 'in') === 'in')>{{ __('Add') }}</option>
                                    <option value="out" @selected($hasOldAdjustment && old('direction') === 'out')>{{ __('Remove') }}</option>
                                </select>
                                @if ($hasOldAdjustment && $errors->has('direction'))
                                    <p class="ui-error">{{ $errors->first('direction') }}</p>
                                @endif
                            </div>
                            <div>
                                <label for="quantity-{{ $product->id }}" class="ui-label">{{ __('Quantity') }} <span class="text-brand-600" aria-hidden="true">*</span></label>
                                <input id="quantity-{{ $product->id }}" name="quantity" type="number" min="1" max="1000000" value="{{ $hasOldAdjustment ? old('quantity') : '' }}" placeholder="{{ __('Qty') }}" required class="ui-input" @if ($hasOldAdjustment && $errors->has('quantity')) aria-invalid="true" @endif>
                                @if ($hasOldAdjustment && $errors->has('quantity'))
                                    <p class="ui-error">{{ $errors->first('quantity') }}</p>
                                @endif
                            </div>
                            <div>
                                <label for="note-{{ $product->id }}" class="ui-label">{{ __('Note (optional)') }}</label>
                                <input id="note-{{ $product->id }}" name="note" value="{{ $hasOldAdjustment ? old('note') : '' }}" maxlength="255" class="ui-input" @if ($hasOldAdjustment && $errors->has('note')) aria-invalid="true" @endif>
                                @if ($hasOldAdjustment && $errors->has('note'))
                                    <p class="ui-error">{{ $errors->first('note') }}</p>
                                @endif
                            </div>
                            <x-ui.button type="submit" variant="primary">{{ __('Apply') }}</x-ui.button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        @error('product_id')
            <p class="ui-error">{{ $message }}</p>
        @enderror
        <div class="ui-pagination mt-6">{{ $products->links() }}</div>
    @endif

    <x-ui.card :padding="false" class="mt-8">
        <div class="border-b border-slate-100 p-5 sm:p-6">
            <h2 class="ui-section-title">{{ __('Recent inventory movements') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('The latest stock additions, removals, and order-related changes.') }}</p>
        </div>
        <x-ui.table :caption="__('Recent inventory movements')" class="rounded-none border-0 shadow-none [&_table]:min-w-[48rem]">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Date') }}</th>
                    <th scope="col">{{ __('Product') }}</th>
                    <th scope="col">{{ __('Movement') }}</th>
                    <th scope="col" class="text-right">{{ __('Quantity') }}</th>
                    <th scope="col">{{ __('Note') }}</th>
                </tr>
            </x-slot:head>
            @forelse ($movements as $movement)
                @php $isRemoval = in_array($movement->type, ['adjustment_out', 'order_placed'], true); @endphp
                <tr>
                    <td class="whitespace-nowrap text-slate-500">
                        @if ($movement->created_at)
                            <time datetime="{{ $movement->created_at->toIso8601String() }}">{{ $movement->created_at->format('M j, Y H:i') }}</time>
                        @else
                            —
                        @endif
                    </td>
                    <td class="font-semibold text-slate-900">{{ $movement->product->name ?? __('Deleted product') }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $movement->type)) }}</td>
                    <td class="text-right">
                        <x-ui.badge :tone="$isRemoval ? 'danger' : 'success'">{{ $isRemoval ? '-' : '+' }}{{ number_format($movement->quantity) }}</x-ui.badge>
                    </td>
                    <td>{{ $movement->note ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-slate-500">{{ __('No inventory movements yet.') }}</td></tr>
            @endforelse
        </x-ui.table>
    </x-ui.card>
@endsection
