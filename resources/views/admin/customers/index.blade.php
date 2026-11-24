@extends('layouts.admin')
@section('title', __('Customers'))
@section('content')
    <x-ui.page-header
        :title="__('Customers')"
        :eyebrow="__('Customer directory')"
        :description="__('Browse customer contact details and registration dates.')"
    />

    @if ($customers->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                :title="__('No customers found')"
                :description="__('Registered customers will appear here when they create an account.')"
            >
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M14 4a4 4 0 010 8M10 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                </x-slot:icon>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <x-ui.table :caption="__('Registered customer contact details')">
            <x-slot:head>
                <tr>
                    <th scope="col">{{ __('Name') }}</th>
                    <th scope="col">{{ __('Email') }}</th>
                    <th scope="col">{{ __('Joined') }}</th>
                </tr>
            </x-slot:head>
            @foreach ($customers as $c)
                <tr>
                    <td class="min-w-48">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-50 text-xs font-bold text-accent-700" aria-hidden="true">{{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}</span>
                            <span class="font-semibold text-slate-900">{{ $c->name }}</span>
                        </div>
                    </td>
                    <td class="min-w-64">
                        @if ($c->email)
                            <a href="mailto:{{ $c->email }}" class="ui-link break-all text-sm">{{ $c->email }}</a>
                        @else
                            <span class="text-sm text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">
                        <time datetime="{{ $c->created_at->toIso8601String() }}">{{ $c->created_at->format('d M Y') }}</time>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="ui-pagination mt-5">{{ $customers->links() }}</div>
    @endif
@endsection