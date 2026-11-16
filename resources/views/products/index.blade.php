@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Products') }}</h1>
    @if ($products->isEmpty())
        <p class="text-gray-500">{{ __('No products available.') }}</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach ($products as $product)
                <div class="border rounded p-4 bg-white">
                    <h3 class="font-semibold">{{ $product->name }}</h3>
                    <p class="text-sm text-gray-600">৳ {{ number_format($product->price, 2) }}</p>
                    <a href="{{ route('products.show', $product) }}" class="text-teal-700 text-sm underline">{{ __('View') }}</a>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    @endif
@endsection