@extends('layouts.vendor')
@section('title', __('Business Profile'))
@section('content')
    <x-ui.page-header
        :title="__('Business Profile')"
        :description="$vendor->vendor_code.' · '.$vendor->verification_status->label()"
        :eyebrow="__('Owner controls')"
    >
        <x-slot:action>
            <x-ui.button :href="route('vendor.registration.status')" variant="outline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M12 3l9 4v5c0 5-3.5 8-9 9-5.5-1-9-4-9-9V7l9-4z"/></svg>
                {{ __('Application status') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <form method="POST" action="{{ route('vendor.settings.update', $vendor) }}">
            @csrf
            @method('PUT')
            <x-ui.card>
                @include('vendor.settings._fields')
                <div class="mt-8 border-t border-slate-100 pt-6">
                    <x-ui.button type="submit" variant="primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Save profile') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </form>

        <x-ui.card>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M12 3l9 4v5c0 5-3.5 8-9 9-5.5-1-9-4-9-9V7l9-4z"/></svg>
            </span>
            <h2 class="mt-4 ui-section-title">{{ __('Business status') }}</h2>
            <dl class="mt-5 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Verification') }}</dt>
                    <dd><x-ui.status>{{ $vendor->verification_status->label() }}</x-ui.status></dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Account') }}</dt>
                    <dd>
                        <x-ui.badge :tone="$vendor->status->value === 'active' ? 'success' : (in_array($vendor->status->value, ['suspended', 'blocked'], true) ? 'danger' : 'neutral')">
                            {{ $vendor->status->label() }}
                        </x-ui.badge>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Vendor code') }}</dt>
                    <dd class="font-mono font-semibold text-slate-900">{{ $vendor->vendor_code }}</dd>
                </div>
            </dl>
            <x-ui.button :href="route('vendor.registration.status')" variant="outline" class="mt-6 w-full">{{ __('Application status') }}</x-ui.button>
            <p class="mt-4 text-xs leading-5 text-slate-500">{{ __('Only the business owner can change profile details.') }}</p>
        </x-ui.card>
    </div>
@endsection
