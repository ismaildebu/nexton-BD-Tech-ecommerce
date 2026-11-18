@extends('layouts.vendor')
@section('title', __('Add product'))
@section('content')
    <h1 class="mb-6 text-2xl font-bold">{{ __('Add product') }}</h1>
    <form method="POST" action="{{ route('vendor.products.store') }}" class="max-w-2xl rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        @csrf
        @include('vendor.products._form')
        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Submit for approval') }}</button>
            <a href="{{ route('vendor.products.index') }}" class="rounded-lg border border-stone-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-stone-50">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection