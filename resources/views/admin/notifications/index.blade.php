@extends('layouts.admin')
@section('title', __('Notifications'))
@section('content')
    <div class="ui-page-wide">
        <x-ui.page-header
            :title="__('Notifications')"
            :description="__('Review messages sent to customers and their read status.')"
            :eyebrow="__('Customer communication')"
        >
            <x-slot:action>
                <x-ui.button :href="route('admin.notifications.create')" variant="secondary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L6 12zm0 0h7.5"/></svg>
                    {{ __('Send notification') }}
                </x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @if ($notifications->count())
            <x-ui.table :caption="__('Notifications sent to customers')" class="[&_table]:min-w-[900px]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Recipient') }}</th>
                        <th scope="col">{{ __('Type') }}</th>
                        <th scope="col">{{ __('Message') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Created') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($notifications as $notification)
                    @php $data = is_array($notification->data) ? $notification->data : []; @endphp
                    <tr class="align-top">
                        <td>
                            <p class="font-semibold text-slate-900">{{ $notification->notifiable?->name ?? __('Unknown recipient') }}</p>
                            <p class="mt-1 break-words text-xs text-slate-500">{{ $notification->notifiable?->email ?? __('ID :id', ['id' => $notification->notifiable_id]) }}</p>
                        </td>
                        <td>
                            <x-ui.badge tone="neutral">
                                {{ match ($notification->type) {
                                    'info' => __('Information'),
                                    'success' => __('Success'),
                                    'warning' => __('Warning'),
                                    default => $notification->type,
                                } }}
                            </x-ui.badge>
                        </td>
                        <td class="max-w-md whitespace-pre-line break-words leading-6">{{ $data['message'] ?? $data['msg'] ?? '—' }}</td>
                        <td>
                            <x-ui.badge :tone="$notification->read_at === null ? 'accent' : 'neutral'">{{ $notification->read_at === null ? __('Unread') : __('Read') }}</x-ui.badge>
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-500">
                            @if ($notification->created_at)
                                <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('d M Y, H:i') }}</time>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No notifications yet')" :description="__('Send a notification to a customer to start the conversation.')">
                    <x-slot:action>
                        <x-ui.button :href="route('admin.notifications.create')" variant="secondary">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L6 12zm0 0h7.5"/></svg>
                            {{ __('Send notification') }}
                        </x-ui.button>
                    </x-slot:action>
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($notifications->hasPages())
            <div class="ui-pagination mt-6">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
