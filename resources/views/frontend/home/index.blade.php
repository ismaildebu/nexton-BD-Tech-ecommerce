@extends('layouts.frontend')
@section('content')
    <section class="py-16 text-center bg-[#253442] text-white rounded-xl mb-10">
        <h1 class="text-5xl font-bold">{{ config('app.name') }}</h1>
        <p class="mt-3 text-white/80 text-lg">{{ __('Your trusted multi-vendor marketplace in Bangladesh') }}</p>
        <form class="mt-6 max-w-xl mx-auto flex" action="{{ route('products.index') }}" method="GET">
            <input type="text" name="q" placeholder="{{ __('Search products...') }}" class="flex-1 px-4 py-3 rounded-l-lg text-[#343A46]">
            <button type="submit" class="bg-[#6852D9] px-6 py-3 rounded-r-lg font-semibold">{{ __('Search') }}</button>
        </form>
    </section>

    <section class="mb-10">
        <h2 class="text-[22px] font-semibold text-[#343A46] mb-4">{{ __('Featured vendor stores') }}</h2>
        @if ($featuredVendors->isEmpty())
            <p class="text-[#8993A4]">{{ __('No vendor stores available yet.') }}</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($featuredVendors as $vendor)
                    <div class="bg-white rounded-xl shadow-sm border border-[#E8EBF0] p-5 hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-full bg-[#6852D9]/10 flex items-center justify-center text-[#6852D9] font-bold mb-3">{{ substr($vendor->business_name, 0, 1) }}</div>
                        <h3 class="font-semibold text-[#343A46]">{{ $vendor->business_name }}</h3>
                        <p class="text-sm text-[#8993A4] mt-1">{{ $vendor->district }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mb-10">
        <h2 class="text-[22px] font-semibold text-[#343A46] mb-4">{{ __('Featured categories') }}</h2>
        @if ($categories->isEmpty())
            <p class="text-[#8993A4]">{{ __('No categories available yet.') }}</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-3">
                @foreach ($categories as $cat)
                    <div class="bg-white rounded-lg shadow-sm border border-[#E8EBF0] p-3 text-center text-sm">{{ $cat->name }}</div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mb-10">
        <h2 class="text-[22px] font-semibold text-[#343A46] mb-4">{{ __('Featured products') }}</h2>
        @if ($featuredProducts->isEmpty())
            <p class="text-[#8993A4]">{{ __('No products available yet.') }}</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach ($featuredProducts as $product)
                    <a href="{{ route('products.show', $product) }}" class="bg-white rounded-xl shadow-sm border border-[#E8EBF0] p-4 hover:shadow-md transition block">
                        <h3 class="font-semibold text-[#343A46] text-sm">{{ $product->name }}</h3>
                        <p class="text-[#6852D9] font-bold mt-2">৳ {{ number_format($product->price, 2) }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection