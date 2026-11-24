@extends('layouts.app')

@section('title', __('Addresses'))

@section('content')
    <x-ui.page-header :title="__('Saved addresses')" :description="__('Manage where your orders are delivered and choose a default address.')"
        :eyebrow="__('Your account')">
        <x-slot:action>
            <x-ui.button :href="route('addresses.create')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                {{ __('Add address') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    <nav class="ui-card mb-7 overflow-hidden p-2" aria-label="{{ __('Account sections') }}">
        <div class="ui-scrollbar flex gap-1 overflow-x-auto">
            @foreach ([
                ['customer.account.index', __('Overview')],
                ['orders.index', __('Orders')],
                ['addresses.index', __('Addresses')],
                ['payments.index', __('Payments')],
                ['shipping.index', __('Shipping')],
                ['reviews.index', __('Reviews')],
            ] as [$routeName, $label])
                <a href="{{ route($routeName) }}" @if(request()->routeIs($routeName)) aria-current="page" @endif
                    class="shrink-0 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 {{ request()->routeIs($routeName) ? 'bg-ink-900 text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">{{ $label }}</a>
            @endforeach
        </div>
    </nav>

    @if ($addresses->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state :title="__('No saved addresses yet')" :description="__('Add your first delivery address to make checkout faster and keep your order details handy.')">
                <x-slot:icon>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
                </x-slot:icon>
                <x-slot:action>
                    <x-ui.button :href="route('addresses.create')">{{ __('Add your first address') }}</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="grid items-start gap-5 md:grid-cols-2">
            @foreach ($addresses as $address)
                <x-ui.card :padding="false" class="{{ $address->is_default ? 'border-brand-200 ring-1 ring-brand-100' : '' }}">
                    <article class="flex h-full flex-col">
                        <div class="flex-1 p-5 sm:p-6">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $address->is_default ? 'bg-brand-50 text-brand-600' : 'bg-accent-50 text-accent-600' }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11.25L12 4l9 7.25M5.25 9.75V20h13.5V9.75M9.75 20v-6h4.5v6"/></svg>
                                    </span>
                                    <x-ui.badge tone="accent">{{ $address->type->label() }}</x-ui.badge>
                                </div>
                                @if ($address->is_default)
                                    <x-ui.badge tone="success">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('Default') }}
                                    </x-ui.badge>
                                @endif
                            </div>

                            <h2 class="mt-5 text-base font-bold text-slate-900">{{ $address->full_name }}</h2>
                            <address class="mt-2 text-sm not-italic leading-6 text-slate-600">
                                <span class="block">{{ $address->address_line }}</span>
                                <span class="block">{{ collect([$address->upazila, $address->district, $address->division, $address->postal_code])->filter()->implode(', ') }}</span>
                            </address>
                            <a href="tel:{{ $address->phone }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-brand-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a1.5 1.5 0 001.5-1.5v-3.75l-4.5-1.5-1.5 2.25a13.5 13.5 0 01-7.5-7.5L9.75 9.75 8.25 5.25H4.5a2.25 2.25 0 00-2.25 2.25z"/></svg>
                                {{ $address->phone }}
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                            @unless ($address->is_default)
                                <form method="POST" action="{{ route('addresses.default', $address) }}"
                                    x-data="{ pending: false }" @submit="pending = true">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.button type="submit" variant="ghost" size="sm" :disabled="false" x-bind:disabled="pending">
                                        <span x-show="pending" x-cloak class="ui-spinner" aria-hidden="true"></span>
                                        {{ __('Make default') }}
                                    </x-ui.button>
                                </form>
                            @endunless
                            <x-ui.button :href="route('addresses.edit', $address)" variant="outline" size="sm">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.86 3.5a2.12 2.12 0 013 3L8 18.36l-4 1 1-4L16.86 3.5z"/></svg>
                                {{ __('Edit') }}
                            </x-ui.button>
                            <form method="POST" action="{{ route('addresses.destroy', $address) }}" class="ml-auto"
                                x-data="{ pending: false }"
                                x-on:submit="if (! confirm(@js(__('Delete this address?')))) { $event.preventDefault(); } else { pending = true; }">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="ghost" size="sm" class="text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                                    :disabled="false" x-bind:disabled="pending">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v5M14 11v5"/></svg>
                                    {{ __('Delete') }}
                                </x-ui.button>
                            </form>
                        </div>
                    </article>
                </x-ui.card>
            @endforeach
        </div>
    @endif
@endsection
