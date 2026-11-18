@extends('layouts.vendor')
@section('title', __('Vendor Dashboard'))
@section('content')
    @if ($vendor)
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">{{ $vendor->business_name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('Vendor Dashboard') }}</p>
            </div>
            <span class="rounded-full bg-teal-50 px-3 py-1 text-sm font-medium text-teal-800 ring-1 ring-teal-200">
                {{ $vendor->verification_status->label() }}
            </span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ __('Total products') }}</p>
                <p class="mt-2 text-3xl font-bold">{{ $stats['products'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ __('Active products') }}</p>
                <p class="mt-2 text-3xl font-bold text-teal-700">{{ $stats['activeProducts'] ?? 0 }}</p>
            </div>
        </div>

        <div class="mt-8 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold">{{ __('Quick actions') }}</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('vendor.products.index') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">{{ __('Manage products') }}</a>
                <a href="{{ route('vendor.documents.index', $vendor) }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-stone-50">{{ __('Documents') }}</a>
                <a href="{{ route('vendor.staff.index', $vendor) }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-stone-50">{{ __('Staff') }}</a>
            </div>
        </div>
    @else
        <div class="mx-auto max-w-lg rounded-xl border border-stone-200 bg-white p-8 text-center shadow-sm">
            <h1 class="text-xl font-bold">{{ __('No vendor profile yet.') }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ __('Register your business to start selling.') }}</p>
            <a href="{{ route('vendor.register.create') }}" class="mt-6 inline-block rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-teal-800">{{ __('Register as vendor') }}</a>
        </div>
    @endif
@endsection