@extends('layouts.vendor')
@section('title', __('Edit product'))
@section('content')
    <x-ui.page-header
        :title="__('Edit product')"
        :description="$product->name"
        :eyebrow="__('Catalog')"
    >
        <x-slot:action>
            <x-ui.button :href="route('vendor.products.index')" variant="outline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
                {{ __('Back to products') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    <form method="POST" action="{{ route('vendor.products.update', $product) }}" class="mx-auto max-w-3xl space-y-5">
        @csrf
        @method('PUT')
        @if ($product->status === 'approved')
            <x-ui.alert tone="warning" :title="__('Changes require approval')">
                {{ __('Saving changes will hide this product from the storefront pending admin re-approval.') }}
            </x-ui.alert>
        @endif
        <x-ui.card>
            @include('vendor.products._form')
            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                <x-ui.button :href="route('vendor.products.index')" variant="outline">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Save changes') }}
                </x-ui.button>
            </div>
        </x-ui.card>
    </form>
@endsection
