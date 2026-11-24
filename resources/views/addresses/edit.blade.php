@extends('layouts.app')

@section('title', __('Edit address'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('addresses.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-brand-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5l-7.5-7.5 7.5-7.5M3 12h18"/></svg>
            {{ __('Back to addresses') }}
        </a>

        <x-ui.page-header :title="__('Edit address')" :description="__('Update this delivery address for your future orders.')"
            :eyebrow="__('Delivery details')" />

        <x-ui.card>
            <form method="POST" action="{{ route('addresses.update', $address) }}" class="space-y-6" novalidate
                x-data="{ pending: false }" @submit="pending = true">
                @csrf
                @method('PUT')
                @include('addresses._form', ['address' => $address])

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <x-ui.button :href="route('addresses.index')" variant="outline">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" :disabled="false" x-bind:disabled="pending" :aria-busy="false"
                        x-bind:aria-busy="pending ? 'true' : 'false'">
                        <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
                        <span x-text="pending ? @js(__('Saving...')) : @js(__('Save changes'))">{{ __('Save changes') }}</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
