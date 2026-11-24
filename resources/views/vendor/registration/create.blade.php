@extends('layouts.app')
@section('title', __('Vendor Registration'))
@section('content')
    <x-ui.page-header
        :title="$vendor ? __('Correct and resubmit application') : __('Vendor Registration')"
        :description="__('Tell us about your business. You can add documents after submitting.')"
        :eyebrow="__('Become a vendor')"
    />

    @if ($vendor?->verification_status?->value === 'rejected' && $vendor->rejection_reason)
        <x-ui.alert tone="error" :title="__('Previous rejection reason')" class="mb-6">
            {{ $vendor->rejection_reason }}
        </x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <form method="POST" action="{{ route('vendor.register.store') }}">
            @csrf
            <x-ui.card>
                @include('vendor.settings._fields')
                <div class="mt-8 border-t border-slate-100 pt-6">
                    <x-ui.button type="submit" variant="primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m-3-9a9 9 0 11-6.7 3M3 4v5h5"/></svg>
                        {{ __('Submit application') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </form>

        <x-ui.card>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M12 3l9 4v5c0 5-3.5 8-9 9-5.5-1-9-4-9-9V7l9-4z"/></svg>
            </span>
            <h2 class="mt-4 ui-section-title">{{ __('What happens next') }}</h2>
            <ol class="mt-5 space-y-5">
                <li class="flex gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">1</span>
                    <div><p class="text-sm font-semibold text-slate-900">{{ __('Submit your business details') }}</p><p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Complete the required fields and send your application for review.') }}</p></div>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent-50 text-xs font-bold text-accent-700">2</span>
                    <div><p class="text-sm font-semibold text-slate-900">{{ __('Add supporting documents') }}</p><p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Upload relevant business documents from your vendor workspace.') }}</p></div>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink-900 text-xs font-bold text-white">3</span>
                    <div><p class="text-sm font-semibold text-slate-900">{{ __('Track your application') }}</p><p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Return to the application status page to see review progress.') }}</p></div>
                </li>
            </ol>
        </x-ui.card>
    </div>
@endsection
