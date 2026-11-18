@extends('layouts.admin')
@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ __('Products') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $pendingCount }} {{ __('awaiting review') }}</p>
        </div>
        <div class="flex gap-2 text-sm">
            @php
                $tab = fn (?string $s) => 'rounded-full px-3 py-1.5 font-medium '
                    . (($status ?? null) === $s ? 'bg-slate-900 text-white' : 'bg-white text-gray-600 ring-1 ring-slate-200 hover:bg-slate-50');
            @endphp
            <a href="{{ route('admin.products.index') }}" class="{{ $tab(null) }}">{{ __('All') }}</a>
            <a href="{{ route('admin.products.index', ['status' => 'pending']) }}" class="{{ $tab('pending') }}">{{ __('Pending') }}</a>
            <a href="{{ route('admin.products.index', ['status' => 'approved']) }}" class="{{ $tab('approved') }}">{{ __('Approved') }}</a>
            <a href="{{ route('admin.products.index', ['status' => 'rejected']) }}" class="{{ $tab('rejected') }}">{{ __('Rejected') }}</a>
        </div>
    </div>

    @if (session('product_notice'))
        <div role="status" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('product_notice') }}</div>
    @endif
    @if (session('product_error'))
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ session('product_error') }}</div>
    @endif

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Vendor') }}</th>
                    <th class="px-4 py-3">{{ __('Category') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Price') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Storefront') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $p)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $p->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->vendor->business_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->category->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">&#2547; {{ number_format($p->price, 2) }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match ($p->status) {
                                    'approved' => 'bg-emerald-50 text-emerald-800',
                                    'rejected' => 'bg-red-50 text-red-800',
                                    default => 'bg-amber-50 text-amber-800',
                                };
                            @endphp
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">{{ ucfirst($p->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs {{ $p->is_active ? 'text-emerald-700' : 'text-gray-400' }}">
                            {{ $p->is_active ? __('Live') : __('Hidden') }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($p->status === 'pending')
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.products.approve', $p) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">{{ __('Approve') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.products.reject', $p) }}" onsubmit="return confirm('{{ __('Reject this product?') }}')">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">{{ __('Reject') }}</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">{{ __('No products found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection