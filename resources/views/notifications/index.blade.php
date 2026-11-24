@extends('layouts.frontend')
@section('title', __('Notifications - :store', ['store' => config('app.name')]))

@section('content')
<div class="ui-page-narrow">
    <x-ui.page-header :title="__('Notifications')" :description="__('Updates about your orders, account, and storefront activity.')" />

    @if ($notifications->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state :title="__('No notifications')" :description="__('New updates will appear here when they are available.')">
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.158c0 .538-.214 1.055-.595 1.437L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </x-slot:icon>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                @php
                    $data = is_array($notification->data) ? $notification->data : [];
                    $message = $data['message'] ?? $data['msg'] ?? $notification->type;
                @endphp
                <article @class(['ui-card p-4 sm:p-5', 'notification-unread border-accent-300 bg-accent-50' => $notification->read_at === null, 'border-slate-200 bg-white' => $notification->read_at !== null])>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex min-w-0 gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $notification->read_at === null ? 'bg-accent-100 text-accent-700' : 'bg-slate-100 text-slate-500' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.158c0 .538-.214 1.055-.595 1.437L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </span>
                            <div class="min-w-0">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <x-ui.badge tone="neutral" class="max-w-full break-words [overflow-wrap:anywhere]">{{ $notification->type }}</x-ui.badge>
                                    @if ($notification->read_at === null)
                                        <x-ui.badge tone="accent">{{ __('New') }}</x-ui.badge>
                                    @endif
                                </div>
                                <p class="whitespace-pre-line break-words text-sm leading-6 text-slate-700">{{ $message }}</p>
                                @if (! empty($data['url']) && is_string($data['url']))
                                    <a href="{{ $data['url'] }}" class="ui-link mt-3 inline-flex min-h-11 items-center text-sm">{{ __('View details') }}</a>
                                @endif
                                <p class="mt-1 text-[11px] text-slate-400"><time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->diffForHumans() }}</time></p>
                            </div>
                        </div>

                        @if ($notification->read_at === null)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="shrink-0 self-start">
                                @csrf
                                @method('PATCH')
                                <x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">{{ __('Mark as read') }}</x-ui.button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="ui-pagination mt-6">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
