@extends('layouts.app')
@section('title', __('Application Status'))
@section('content')
    <x-ui.page-header
        :title="__('Application Status')"
        :description="__('Track your vendor application and see what you need to do next.')"
        :eyebrow="__('Vendor onboarding')"
    >
        @if ($vendor)
            <x-slot:action>
                <x-ui.status :status="$vendor->verification_status->label()" />
                <x-ui.badge :tone="match ($vendor->status->value) {
                    'active' => 'success',
                    'blocked', 'suspended' => 'danger',
                    default => 'neutral',
                }">
                    {{ $vendor->status->label() }}
                </x-ui.badge>
            </x-slot:action>
        @endif
    </x-ui.page-header>

    @if ($vendor)
        @php
            $canCorrect = in_array($vendor->verification_status, [
                \App\Modules\Vendor\Enums\VendorVerificationStatus::Draft,
                \App\Modules\Vendor\Enums\VendorVerificationStatus::Rejected,
            ], true);
            $isRejected = $vendor->verification_status === \App\Modules\Vendor\Enums\VendorVerificationStatus::Rejected;
            $statusTone = match ($vendor->verification_status->value) {
                'approved' => 'success',
                'rejected', 'suspended' => 'error',
                'draft' => 'info',
                default => 'warning',
            };
            $statusMessage = match ($vendor->verification_status->value) {
                'draft' => __('Complete your business details and submit the application for review.'),
                'pending' => __('Your application has been submitted and is awaiting review.'),
                'under_review' => __('Your application is currently being reviewed by the admin team.'),
                'approved' => __('Your vendor application is approved. You can manage your workspace and business profile.'),
                'rejected' => __('Review the rejection reason, correct your application, and resubmit it.'),
                'suspended' => __('Your vendor application is suspended. Contact support for assistance.'),
            };
        @endphp

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="space-y-6">
                <x-ui.alert :tone="$statusTone" :title="$vendor->verification_status->label()">
                    {{ $statusMessage }}
                </x-ui.alert>

                @if ($isRejected && $vendor->rejection_reason)
                    <x-ui.alert tone="error" :title="__('Rejection reason')">
                        {{ $vendor->rejection_reason }}
                    </x-ui.alert>
                @endif

                <x-ui.card :padding="false">
                    <div class="border-b border-slate-100 p-5 sm:p-6">
                        <h2 class="ui-section-title">{{ __('Application details') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('The information submitted for your vendor account.') }}</p>
                    </div>
                    <dl class="divide-y divide-slate-100 px-5 sm:px-6">
                        <div class="flex items-center justify-between gap-4 py-4 text-sm">
                            <dt class="text-slate-500">{{ __('Business') }}</dt>
                            <dd class="text-right font-semibold text-slate-900">{{ $vendor->business_name }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-4 text-sm">
                            <dt class="text-slate-500">{{ __('Vendor code') }}</dt>
                            <dd class="font-mono text-slate-800">{{ $vendor->vendor_code }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-4 text-sm">
                            <dt class="text-slate-500">{{ __('Application status') }}</dt>
                            <dd><x-ui.status :status="$vendor->verification_status->label()" /></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-4 text-sm">
                            <dt class="text-slate-500">{{ __('Account status') }}</dt>
                            <dd class="font-semibold text-slate-800">{{ $vendor->status->label() }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
            </div>

            <x-ui.card>
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    </span>
                    <h2 class="ui-section-title">{{ __('Next steps') }}</h2>
                </div>
                <p class="mt-5 text-sm leading-6 text-slate-600">{{ $statusMessage }}</p>
                <div class="mt-6 space-y-3">
                    @if ($canCorrect)
                        <x-ui.button :href="route('vendor.register.create')" variant="primary" class="w-full">
                            {{ __('Correct application') }}
                        </x-ui.button>
                    @endif
                    @if ($isRejected)
                        <form method="POST" action="{{ route('vendor.registration.resubmit') }}">
                            @csrf
                            <x-ui.button type="submit" variant="outline" class="w-full">
                                {{ __('Resubmit for review') }}
                            </x-ui.button>
                        </form>
                    @endif
                    <x-ui.button :href="route('vendor.settings.edit', $vendor)" variant="outline" class="w-full">
                        {{ __('Business profile settings') }}
                    </x-ui.button>
                    <x-ui.button :href="route('vendor.documents.index', $vendor)" variant="outline" class="w-full">
                        {{ __('Documents') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    @else
        <x-ui.card>
            <x-ui.empty-state
                :title="__('No application found.')"
                :description="__('Register your business to submit an application and track its review status.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h4M7 3h7l5 5v13H7V3zM14 3v5h5"/></svg>
                </x-slot:icon>
                <x-slot:action>
                    <x-ui.button :href="route('vendor.register.create')" variant="primary">
                        {{ __('Register as vendor') }}
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @endif
@endsection
