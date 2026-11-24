@extends('layouts.admin')
@section('title', __('Inventory'))

@section('content')
    <div class="ui-page">
        <x-ui.page-header
            :title="__('Inventory')"
            :description="__('Monitor stock quantities across your product catalog.')"
            :eyebrow="__('Catalog operations')"
        />

        <x-ui.card>
            @if ($items->isEmpty())
                <x-ui.empty-state
                    :title="__('No inventory records yet')"
                    :description="__('Stock records will appear here once products are added to the catalog.')"
                />
            @else
                <x-ui.table :caption="__('Product inventory quantities')">
                    <x-slot:head>
                        <tr>
                            <th scope="col">{{ __('Product') }}</th>
                            <th scope="col" class="text-right">{{ __('Quantity') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($items as $item)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $item->product->name ?? __('Unavailable product') }}</td>
                            <td class="text-right">
                                <x-ui.badge :tone="$item->quantity > 0 ? 'success' : 'danger'">
                                    {{ $item->quantity }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
                <div class="ui-pagination">{{ $items->links() }}</div>
            @endif
        </x-ui.card>
    </div>
@endsection
