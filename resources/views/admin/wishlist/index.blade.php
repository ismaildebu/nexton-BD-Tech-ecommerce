@extends('layouts.admin')

@section('title', __('Wishlist'))

@section('content')
    <div class="ui-page">
        <x-ui.page-header
            :title="__('Wishlist')"
            :description="__('See which products customers have saved for later.')"
            :eyebrow="__('Customer insights')"
        />

        @if ($items->count())
            <x-ui.table :caption="__('Products saved to customer wishlists')">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Customer') }}</th>
                        <th scope="col">{{ __('Product') }}</th>
                        <th scope="col">{{ __('Saved') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($items as $w)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-100 text-sm font-bold text-accent-700" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($w->user?->name ?? '—', 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $w->user?->name ?? __('Deleted user') }}</p>
                                    <p class="mt-0.5 break-words text-xs text-slate-500">{{ $w->user?->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $w->product?->name ?? __('Deleted product') }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $w->product?->vendor?->business_name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-500">
                            @if ($w->created_at)
                                <time datetime="{{ $w->created_at->toIso8601String() }}">{{ $w->created_at->format('d M Y') }}</time>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No wishlist items yet')" :description="__('Saved products will appear here when customers add them to their wishlists.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($items->hasPages())
            <div class="ui-pagination mt-6">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
