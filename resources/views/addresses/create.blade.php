@extends('layouts.app')

@section('title', __('Add address'))

@section('content')
    <section class="mx-auto max-w-xl rounded-lg border border-stone-200 bg-white p-6">
        <h1 class="mb-4 text-lg font-semibold">{{ __('Add address') }}</h1>

        <form method="POST" action="{{ route('addresses.store') }}" class="space-y-4" novalidate>
            @csrf
            @include('addresses._form')

            <div class="flex items-center gap-4">
                <button type="submit" class="rounded-md bg-teal-700 px-4 py-2 font-medium text-white hover:bg-teal-800">{{ __('Save address') }}</button>
                <a href="{{ route('addresses.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
            </div>
        </form>
    </section>
@endsection
