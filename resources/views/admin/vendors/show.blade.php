@extends('layouts.admin')

@section('title', __('Vendor details'))

@section('content')
    @php
        $verification = $vendor->verification_status->value;
        $canReject = in_array($verification, ['pending', 'under_review', 'approved'], true) && ! in_array($vendor->status->value, ['suspended', 'blocked'], true);
    @endphp

    <div class="ui-page">
        <a href="{{ route('admin.vendors.index') }}" class="mb-5 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7 7-7m-7 7h18"/></svg>
            {{ __('Back to vendors') }}
        </a>

        <x-ui.page-header :title="$vendor->business_name" :description="$vendor->vendor_code" :eyebrow="__('Vendor verification')">
            <x-slot:action>
                <x-ui.status>{{ $vendor->verification_status->label() }}</x-ui.status>
                @if ($vendor->status->value === 'blocked')
                    <x-ui.badge tone="danger">{{ $vendor->status->label() }}</x-ui.badge>
                @else
                    <x-ui.status>{{ $vendor->status->label() }}</x-ui.status>
                @endif
            </x-slot:action>
        </x-ui.page-header>

        @if ($verification === 'rejected' && $vendor->rejection_reason)
            <x-ui.alert tone="error" class="mb-6" :title="__('Rejection reason')">{{ $vendor->rejection_reason }}</x-ui.alert>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-ui.card :padding="false">
                    <div class="flex items-center gap-3 border-b border-slate-100 p-5 sm:p-6">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>
                        </span>
                        <h2 class="ui-section-title">{{ __('Business details') }}</h2>
                    </div>
                    <dl class="grid gap-5 p-5 text-sm sm:grid-cols-2 sm:p-6">
                        @foreach (['business_type' => __('Business type'), 'business_email' => __('Business email'), 'business_phone' => __('Business phone'), 'contact_person' => __('Contact person'), 'district' => __('District'), 'upazila' => __('Upazila'), 'postal_code' => __('Postal code')] as $field => $label)
                            <div class="min-w-0">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
                                <dd class="mt-1.5 break-words font-semibold text-slate-800">{{ $vendor->$field ?? '—' }}</dd>
                            </div>
                        @endforeach
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Business address') }}</dt>
                            <dd class="mt-1.5 whitespace-pre-line leading-6 text-slate-700">{{ $vendor->business_address ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Description') }}</dt>
                            <dd class="mt-1.5 whitespace-pre-line leading-6 text-slate-700">{{ $vendor->business_description ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-ui.card>

                <x-ui.card :padding="false">
                    <div class="border-b border-slate-100 p-5 sm:p-6">
                        <h2 class="ui-section-title">{{ __('Documents') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Review the files submitted with this vendor application.') }}</p>
                    </div>
                    @if ($vendor->documents->count())
                        <x-ui.table :caption="__('Documents submitted by this vendor')" class="rounded-none border-0 shadow-none">
                            <x-slot:head>
                                <tr>
                                    <th scope="col">{{ __('Document') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Size') }}</th>
                                    <th scope="col" class="text-right">{{ __('Actions') }}</th>
                                </tr>
                            </x-slot:head>
                            @foreach ($vendor->documents as $document)
                                <tr>
                                    <td>
                                        <p class="max-w-xs break-words font-semibold text-slate-900">{{ $document->original_filename }}</p>
                                        <p class="mt-1 text-xs capitalize text-slate-500">{{ str_replace('_', ' ', $document->document_type) }}</p>
                                    </td>
                                    <td><x-ui.status>{{ __(ucfirst($document->verification_status)) }}</x-ui.status></td>
                                    <td class="whitespace-nowrap text-xs text-slate-500">{{ $document->file_size !== null ? number_format($document->file_size / 1024, 0).' '.__('KB') : '—' }}</td>
                                    <td class="text-right">
                                        <x-ui.button :href="route('admin.vendors.documents.preview', [$vendor, $document])" target="_blank" rel="noopener" variant="outline" size="sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V11.25M13.5 6v5.25H18.75M13.5 6L18.75 11.25"/></svg>
                                            {{ __('Secure preview') }}
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    @else
                        <x-ui.empty-state :title="__('No documents uploaded')" :description="__('Files submitted for verification will be available here.')">
                            <x-slot:icon>
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25v-2.25"/></svg>
                            </x-slot:icon>
                        </x-ui.empty-state>
                    @endif
                </x-ui.card>

                <x-ui.card :padding="false">
                    <div class="border-b border-slate-100 p-5 sm:p-6">
                        <h2 class="ui-section-title">{{ __('Status history') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('The latest verification and account changes for this vendor.') }}</p>
                    </div>
                    @forelse ($vendor->statusHistories as $history)
                        <div class="flex gap-4 border-b border-slate-100 p-5 last:border-b-0 sm:px-6">
                            <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500" aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold text-slate-500">{{ $history->previous_verification_status ? str_replace('_', ' ', ucfirst($history->previous_verification_status)) : '—' }}</span>
                                    <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5l5 5-5 5"/></svg>
                                    @if ($history->new_verification_status)
                                        <x-ui.status>{{ __(str_replace('_', ' ', ucfirst($history->new_verification_status))) }}</x-ui.status>
                                    @else
                                        <span class="text-xs font-semibold text-slate-500">—</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-xs text-slate-500">
                                    <span class="font-semibold text-slate-600">{{ __('Account') }}:</span>
                                    {{ $history->previous_account_status ? str_replace('_', ' ', ucfirst($history->previous_account_status)) : '—' }}
                                    → {{ $history->new_account_status ? str_replace('_', ' ', ucfirst($history->new_account_status)) : '—' }}
                                </p>
                                @if ($history->reason)
                                    <p class="mt-2 whitespace-pre-line break-words rounded-xl bg-slate-50 p-3 text-sm leading-6 text-slate-600">{{ $history->reason }}</p>
                                @endif
                                @if ($history->created_at)
                                    <time class="mt-3 block text-xs text-slate-400" datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('d M Y, H:i') }}</time>
                                @endif
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state :title="__('No status changes recorded')" :description="__('Verification and account changes will be listed here.')" />
                    @endforelse
                </x-ui.card>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-28" aria-label="{{ __('Vendor review information') }}">
                <x-ui.card :padding="false">
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="ui-section-title">{{ __('Review actions') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Update this vendor’s verification or account status.') }}</p>
                    </div>
                    <div class="space-y-6 p-5">
                        @php
                            $reasonAction = old('reason_action');
                            $rejectError = $reasonAction === 'reject' && $errors->has('reason');
                            $suspendError = $reasonAction === 'suspend' && $errors->has('reason');
                        @endphp
                        @if ((in_array($verification, ['pending', 'under_review'], true) && $vendor->status->value === 'inactive') || $canReject || ($verification === 'approved' && $vendor->status->value === 'active'))
                            @if (in_array($verification, ['pending', 'under_review'], true) && $vendor->status->value === 'inactive')
                                <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="success" class="w-full">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-9"/></svg>
                                        {{ __('Approve vendor') }}
                                    </x-ui.button>
                                </form>
                            @endif

                            @if ($canReject)
                                <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="reason_action" value="reject">
                                    <div>
                                        <label for="reject_reason" class="ui-label">{{ __('Rejection reason') }} <span class="text-brand-600" aria-hidden="true">*</span></label>
                                        <input id="reject_reason" type="text" name="reason" value="{{ $rejectError ? old('reason') : '' }}" required maxlength="1000" class="ui-input" aria-describedby="reject_reason-help{{ $rejectError ? ' reject_reason-error' : '' }}" @if ($rejectError) aria-invalid="true" @endif>
                                        <p id="reject_reason-help" class="ui-help">{{ __('Explain why the application is being rejected.') }}</p>
                                        @if ($rejectError)
                                            <p id="reject_reason-error" class="ui-error">{{ $errors->first('reason') }}</p>
                                        @endif
                                    </div>
                                    <x-ui.button type="submit" variant="danger" class="w-full">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        {{ __('Reject vendor') }}
                                    </x-ui.button>
                                </form>
                            @endif

                            @if ($verification === 'approved' && $vendor->status->value === 'active')
                                <form method="POST" action="{{ route('admin.vendors.suspend', $vendor) }}" class="space-y-3 border-t border-slate-100 pt-6">
                                    @csrf
                                    <input type="hidden" name="reason_action" value="suspend">
                                    <div>
                                        <label for="suspend_reason" class="ui-label">{{ __('Suspension reason') }} <span class="text-brand-600" aria-hidden="true">*</span></label>
                                        <input id="suspend_reason" type="text" name="reason" value="{{ $suspendError ? old('reason') : '' }}" required maxlength="1000" aria-describedby="suspend_reason-help{{ $suspendError ? ' suspend_reason-error' : '' }}" @if ($suspendError) aria-invalid="true" @endif>
                                        <p id="suspend_reason-help" class="ui-help">{{ __('Suspending a vendor also deactivates their products.') }}</p>
                                        @if ($suspendError)
                                            <p id="suspend_reason-error" class="ui-error">{{ $errors->first('reason') }}</p>
                                        @endif
                                    </div>
                                    <x-ui.button type="submit" variant="outline" class="w-full border-rose-200 text-rose-600 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m5-9l-3 12H8L5 6m2 3h10"/></svg>
                                        {{ __('Suspend vendor') }}
                                    </x-ui.button>
                                </form>
                            @endif
                        @else
                            <div class="rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-500">
                                <svg class="mb-3 h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('No review actions are available for this vendor’s current status.') }}
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <x-ui.card :padding="false">
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="ui-section-title">{{ __('Key dates') }}</h2>
                    </div>
                    <dl class="space-y-4 p-5 text-sm">
                        @foreach (['submitted_at' => __('Submitted'), 'approved_at' => __('Approved'), 'rejected_at' => __('Rejected')] as $field => $label)
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">{{ $label }}</dt>
                                <dd class="text-right font-semibold text-slate-800">{{ $vendor->$field?->format('d M Y, H:i') ?? '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-ui.card>
            </aside>
        </div>
    </div>
@endsection
