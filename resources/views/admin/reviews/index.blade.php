@extends('layouts.admin')
@section('title', __('Reviews'))
@section('content')
    <div class="ui-page-wide">
        <x-ui.page-header
            :title="__('Reviews')"
            :description="__('Monitor customer feedback and remove reviews that violate store guidelines.')"
            :eyebrow="__('Content moderation')"
        />

        @if ($reviews->count())
            <x-ui.table :caption="__('Customer product reviews')" class="[&_table]:min-w-[960px]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Product') }}</th>
                        <th scope="col">{{ __('Customer') }}</th>
                        <th scope="col">{{ __('Rating') }}</th>
                        <th scope="col">{{ __('Comment') }}</th>
                        <th scope="col">{{ __('Submitted') }}</th>
                        <th scope="col" class="text-right">{{ __('Action') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($reviews as $review)
                    <tr class="align-top">
                        <td>
                            <p class="font-semibold text-slate-900">{{ $review->product?->name ?? __('Deleted product') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $review->product?->vendor?->business_name ?? '—' }}</p>
                        </td>
                        <td>
                            <p class="font-semibold text-slate-800">{{ $review->user?->name ?? __('Deleted user') }}</p>
                            <p class="mt-1 break-words text-xs text-slate-500">{{ $review->user?->email ?? '—' }}</p>
                        </td>
                        <td>
                            <x-ui.badge tone="warning" :aria-label="__(':rating out of 5 stars', ['rating' => $review->rating])">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L3.098 10.1c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                {{ $review->rating }}/5
                            </x-ui.badge>
                        </td>
                        <td class="max-w-sm whitespace-pre-line break-words leading-6">{{ $review->comment ?: '—' }}</td>
                        <td class="whitespace-nowrap text-xs text-slate-500">
                            @if ($review->created_at)
                                <time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->format('d M Y') }}</time>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('{{ __('Delete this review?') }}')">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="outline" size="sm" class="border-rose-200 text-rose-600 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.25 9m12.75-3H1.5l1.64 16.5h11.72L18 6zM5 6l.75-3h8.5L15 6"/></svg>
                                    {{ __('Delete') }}
                                </x-ui.button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No reviews yet')" :description="__('Customer product reviews will appear here for moderation.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($reviews->hasPages())
            <div class="ui-pagination mt-6">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
