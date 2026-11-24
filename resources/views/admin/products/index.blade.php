@extends('layouts.admin')
@section('title', __('Products'))
@section('content')
    <x-ui.page-header
        :title="__('Products')"
        :eyebrow="__('Catalog moderation')"
        :description="__('Awaiting review: :count', ['count' => number_format($pendingCount)])"
    >
        <x-slot:action>
            <x-ui.badge :tone="$pendingCount > 0 ? 'warning' : 'success'">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ number_format($pendingCount) }} {{ __('pending review') }}
            </x-ui.badge>
        </x-slot:action>
    </x-ui.page-header>

    @if (session('product_notice'))
        <x-ui.alert tone="success" class="mb-5" role="status">{{ session('product_notice') }}</x-ui.alert>
    @endif
    @if (session('product_error'))
        <x-ui.alert tone="error" class="mb-5">{{ session('product_error') }}</x-ui.alert>
    @endif

    <nav class="mb-5 flex flex-wrap gap-2" aria-label="{{ __('Filter products by status') }}">
        @php
            $tabs = [
                null => __('All'),
                'pending' => __('Pending'),
                'approved' => __('Approved'),
                'rejected' => __('Rejected'),
            ];
        @endphp
        @foreach ($tabs as $value => $label)
            <x-ui.button
                :href="route('admin.products.index', $value ? ['status' => $value] : [])"
                :variant="($status ?? null) === $value ? 'dark' : 'outline'"
                size="sm"
                class="min-h-10"
                :aria-current="($status ?? null) === $value ? 'page' : null"
            >{{ $label }}</x-ui.button>
        @endforeach
    </nav>

    @if ($products->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                :title="$status ? __('No :status products found', ['status' => __(ucfirst($status))]) : __('No products found')"
                :description="$status ? __('Try another status filter or view the full catalog.') : __('Product submissions will appear here when they are available.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4"/></svg>
                </x-slot:icon>
                @if ($status)
                    <x-slot:action>
                        <x-ui.button :href="route('admin.products.index')" variant="outline">{{ __('View all products') }}</x-ui.button>
                    </x-slot:action>
                @endif
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Product review queue')">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Name') }}</th>
                    <th scope="col">{{ __('Vendor') }}</th>
                    <th scope="col">{{ __('Category') }}</th>
                    <th scope="col" class="text-right">{{ __('Price') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col">{{ __('Storefront') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </x-slot:head>
            @foreach ($products as $p)
                <tr>
                    <td class="min-w-48 font-semibold text-slate-900">{{ $p->name }}</td>
                    <td>{{ $p->vendor->business_name ?? '—' }}</td>
                    <td>{{ $p->category->name ?? '—' }}</td>
                    <td class="whitespace-nowrap text-right font-semibold text-slate-900">৳ {{ number_format($p->price, 2) }}</td>
                    <td>
                        <x-ui.status>{{ __(ucfirst($p->status)) }}</x-ui.status>
                    </td>
                    <td>
                        <x-ui.badge :tone="$p->is_active ? 'success' : 'neutral'">
                            <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                            {{ $p->is_active ? __('Live') : __('Hidden') }}
                        </x-ui.badge>
                    </td>
                    <td class="text-right">
                        @if ($p->status === 'pending')
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('admin.products.approve', $p) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="success" size="sm" class="min-h-10">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('Approve') }}
                                    </x-ui.button>
                                </form>
                                <form method="POST" action="{{ route('admin.products.reject', $p) }}" onsubmit="return confirm('{{ __('Reject this product?') }}')">
                                    @csrf
                                    <x-ui.button type="submit" variant="outline" size="sm" class="min-h-10 text-rose-700 hover:border-rose-200 hover:bg-rose-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        {{ __('Reject') }}
                                    </x-ui.button>
                                </form>
                            </div>
                        @else
                            <span class="text-xs text-slate-400">{{ __('No action available') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="ui-pagination mt-5">{{ $products->links() }}</div>
    @endif
@endsection