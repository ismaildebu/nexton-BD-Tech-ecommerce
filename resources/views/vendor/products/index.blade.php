@extends('layouts.vendor')
@section('title', __('My products'))
@section('content')
    <x-ui.page-header
        :title="__('My products')"
        :description="__('Manage your catalog, pricing, and product approval status.')"
        :eyebrow="__('Catalog')"
    >
        <x-slot:action>
            <x-ui.button :href="route('vendor.products.create')" variant="primary">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ __('Add product') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    @if ($products->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state
                :title="__('You have not added any products yet.')"
                :description="__('Create your first product and submit it for admin approval.')"
            >
                <x-slot:action>
                    <x-ui.button :href="route('vendor.products.create')" variant="primary">{{ __('Add product') }}</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Your products, pricing, and approval status')" class="[&_table]:min-w-[56rem]">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Name') }}</th>
                    <th scope="col">{{ __('Category') }}</th>
                    <th scope="col" class="text-right">{{ __('Price') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col" class="text-right"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </x-slot:head>
            @foreach ($products as $product)
                <tr>
                    <td class="font-semibold text-slate-900">{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td class="whitespace-nowrap text-right font-semibold tabular-nums text-slate-900">৳ {{ number_format($product->price, 2) }}</td>
                    <td>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.status :status="$product->status" />
                            <x-ui.badge :tone="$product->is_active ? 'success' : 'neutral'">
                                {{ $product->is_active ? __('Live') : __('Hidden') }}
                            </x-ui.badge>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <x-ui.button :href="route('vendor.products.edit', $product)" variant="outline">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-9.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 6.5-6.5z"/></svg>
                                {{ __('Edit') }}
                            </x-ui.button>
                            @if ($product->is_active)
                                <form method="POST" action="{{ route('vendor.products.destroy', $product) }}" onsubmit="return confirm('{{ __('Deactivate this product?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="outline" class="text-rose-700 hover:border-rose-200 hover:bg-rose-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6L6 18M6 6l12 12"/></svg>
                                        {{ __('Deactivate') }}
                                    </x-ui.button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="ui-pagination mt-6">{{ $products->links() }}</div>
    @endif
@endsection
