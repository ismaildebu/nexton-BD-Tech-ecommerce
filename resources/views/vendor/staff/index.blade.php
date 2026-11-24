@extends('layouts.vendor')
@section('title', __('Staff'))
@section('content')
    <x-ui.page-header
        :title="__('Staff')"
        :description="__('Invite team members, assign roles, and manage pending invitations.')"
        :eyebrow="__('Owner controls')"
    />

    @if (session('invitation_token'))
        <x-ui.alert tone="warning" :title="__('Deliver this one-time invitation link to the invitee securely. It will not be shown again.')" class="mb-6">
            <a href="{{ route('vendor.staff.invitation', session('invitation_token')) }}" class="mt-2 block break-all font-semibold underline underline-offset-4 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-amber-500/20">{{ route('vendor.staff.invitation', session('invitation_token')) }}</a>
        </x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <x-ui.card>
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8v6m-3-3h6M4 6h16v12H4V6zm3 6h.01M10 12h.01M13 12h.01M16 12h.01M7 15h10"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Invite a team member') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('The invitee must already have an account with this email address.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('vendor.staff.store', $vendor) }}" class="mt-6 grid gap-5 sm:grid-cols-[minmax(0,1fr)_10rem] sm:items-end">
                @csrf
                <x-form.input name="email" :label="__('Account email')" type="email" maxlength="255" autocomplete="email" required />
                <x-form.select name="staff_role" :label="__('Role')" required>
                    <option value="staff" @selected(old('staff_role', 'staff') === 'staff')>{{ __('Staff') }}</option>
                    <option value="manager" @selected(old('staff_role') === 'manager')>{{ __('Manager') }}</option>
                </x-form.select>
                <div class="sm:col-span-2">
                    <x-ui.button type="submit" variant="primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8v6m-3-3h6M4 6h16v12H4V6zm3 6h.01M10 12h.01M13 12h.01M16 12h.01M7 15h10"/></svg>
                        {{ __('Send invitation') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M14 4a4 4 0 010 8M10 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
            </span>
            <h2 class="mt-4 ui-section-title">{{ __('Team access') }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Invitations expire after seven days. Staff can access the workspace only after accepting their invitation.') }}</p>
            <p class="mt-4 text-xs leading-5 text-slate-500">{{ __('Staff management and business profile settings are available to the vendor owner only.') }}</p>
        </x-ui.card>
    </div>

    <x-ui.card :padding="false" class="mt-8">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6">
            <h2 class="ui-section-title">{{ __('Team members') }}</h2>
            <span class="text-sm font-semibold tabular-nums text-slate-500">{{ number_format($staff->count()) }}</span>
        </div>
        @if ($staff->isEmpty())
            <x-ui.empty-state
                :title="__('No staff invitations or members yet.')"
                :description="__('Send an invitation above to add someone to your vendor team.')"
            />
        @else
            <x-ui.table :caption="__('Vendor team members and invitations')" class="rounded-none border-0 shadow-none [&_table]:min-w-[44rem]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Member') }}</th>
                        <th scope="col">{{ __('Role') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col" class="text-right"><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </x-slot:head>
                @foreach ($staff as $member)
                    <tr>
                        <td>
                            <p class="font-semibold text-slate-900">{{ $member->user?->name ?? $member->invited_email ?? __('Deleted account') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $member->invited_email ?? $member->user?->email }}</p>
                        </td>
                        <td>{{ ucfirst($member->staff_role) }}</td>
                        <td>
                            <x-ui.status :status="$member->status" />
                            @if ($member->status === 'invited' && $member->invitation_expires_at)
                                <p class="mt-1.5 text-xs {{ $member->invitation_expires_at->isPast() ? 'font-semibold text-rose-600' : 'text-slate-500' }}">
                                    {{ $member->invitation_expires_at->isPast() ? __('Expired') : __('Expires') }}
                                    <time datetime="{{ $member->invitation_expires_at->toIso8601String() }}">{{ $member->invitation_expires_at->format('M j, Y') }}</time>
                                </p>
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($member->status === 'invited')
                                <form method="POST" action="{{ route('vendor.staff.revoke', [$vendor, $member]) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="outline" class="text-rose-700 hover:border-rose-200 hover:bg-rose-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6L6 18M6 6l12 12"/></svg>
                                        {{ __('Revoke') }}
                                    </x-ui.button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>
@endsection
