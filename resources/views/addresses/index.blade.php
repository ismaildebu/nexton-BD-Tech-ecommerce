@extends('layouts.app')

@section('title', __('Addresses'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-lg font-semibold">{{ __('Addresses') }}</h1>
        <a href="{{ route('addresses.create') }}" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Add address') }}</a>
    </div>

    @forelse ($addresses as $address)
        <article class="mb-4 rounded-lg border border-stone-200 bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-medium">
                        {{ $address->full_name }}
                        <span class="ml-2 text-sm font-normal text-gray-500">{{ $address->type->label() }}</span>
                        @if ($address->is_default)
                            <span class="ml-2 rounded bg-teal-50 px-2 py-0.5 text-xs font-medium text-teal-800">{{ __('Default') }}</span>
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-700">{{ $address->address_line }}</p>
                    <p class="text-sm text-gray-700">
                        {{ collect([$address->upazila, $address->district, $address->division, $address->postal_code])->filter()->implode(', ') }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">{{ $address->phone }}</p>
                </div>

                <div class="flex items-center gap-4 text-sm">
                    @unless ($address->is_default)
                        <form method="POST" action="{{ route('addresses.default', $address) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-teal-800 hover:underline">{{ __('Make default') }}</button>
                        </form>
                    @endunless

                    <a href="{{ route('addresses.edit', $address) }}" class="text-teal-800 hover:underline">{{ __('Edit') }}</a>

                    <form method="POST" action="{{ route('addresses.destroy', $address) }}"
                        x-data x-on:submit="if (! confirm('{{ __('Delete this address?') }}')) $event.preventDefault()">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-700 hover:underline">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-lg border border-dashed border-stone-300 p-8 text-center">
            <p class="text-gray-700">{{ __('You have not saved an address yet.') }}</p>
            <a href="{{ route('addresses.create') }}" class="mt-3 inline-block text-teal-800 hover:underline">{{ __('Add your first address') }}</a>
        </div>
    @endforelse
@endsection
