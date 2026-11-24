@extends('layouts.app')
@section('title', __('Staff Invitation'))
@section('content')
    <div class="mx-auto max-w-2xl">
        <x-ui.page-header
            :title="__('Staff Invitation')"
            :description="__('You have been invited to join :vendor as :role.', ['vendor' => $invitation->vendor->business_name, 'role' => ucfirst($invitation->staff_role)])"
            :eyebrow="__('Vendor team')"
        >
            <x-slot:action>
                <x-ui.badge tone="accent">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('Pending invitation') }}
                </x-ui.badge>
            </x-slot:action>
        </x-ui.page-header>

        <x-ui.card>
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-ink-900 text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8v6m-3-3h6M4 6h16v12H4V6zm3 6h.01M10 12h.01M13 12h.01M16 12h.01M7 15h10"/></svg>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $invitation->vendor->business_name }}</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('Review the invitation details before accepting or declining.') }}</p>
                </div>
            </div>

            <dl class="mt-6 grid gap-4 rounded-2xl bg-slate-50 p-5 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Role') }}</dt>
                    <dd class="mt-1.5 font-semibold text-slate-900">{{ ucfirst($invitation->staff_role) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Invitation expires') }}</dt>
                    <dd class="mt-1.5 font-semibold text-slate-900">
                        <time datetime="{{ $invitation->invitation_expires_at->toIso8601String() }}">{{ $invitation->invitation_expires_at->format('M j, Y H:i') }}</time>
                    </dd>
                </div>
            </dl>

            @if ($isInvitee)
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <form method="POST" action="{{ route('vendor.staff.invitation.accept', $token) }}">
                        @csrf
                        <x-ui.button type="submit" variant="primary">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ __('Accept invitation') }}
                        </x-ui.button>
                    </form>
                    <form method="POST" action="{{ route('vendor.staff.invitation.decline', $token) }}">
                        @csrf
                        <x-ui.button type="submit" variant="outline">{{ __('Decline') }}</x-ui.button>
                    </form>
                </div>
            @else
                <x-ui.alert tone="warning" class="mt-8">
                    {{ __('Sign in with the invited account to respond to this invitation.') }}
                </x-ui.alert>
            @endif
        </x-ui.card>
    </div>
@endsection
