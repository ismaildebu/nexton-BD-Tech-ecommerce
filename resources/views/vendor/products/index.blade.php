@extends('layouts.vendor')
@section('title', __('My products'))
@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">{{ __('My products') }}</h1>
        <a href="{{ route('vendor.products.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">{{ __('Add product') }}</a>
    </div>

    @if ($products->isEmpty())
        <div class="rounded-xl border border-stone-200 bg-white p-10 text-center shadow-sm">
            <p class="text-gray-500">{{ __('You have not added any products yet.') }}</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Category') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Price') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($products as $product)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $product->category->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">৳ {{ number_format($product->price, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $product->is_active ? 'bg-teal-50 text-teal-800' : 'bg-amber-50 text-amber-800' }}">
                                    {{ $product->is_active ? __('Live') : ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('vendor.products.edit', $product) }}" class="text-teal-700 hover:underline">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    @endif
@endsection