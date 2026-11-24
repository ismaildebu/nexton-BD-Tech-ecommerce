@extends('layouts.frontend')

@section('title', __('Your reviews'))

@section('content')
    <div class="ui-page">
        <x-ui.page-header :title="__('Your reviews')" :description="__('Revisit the products you reviewed and the feedback you shared with other shoppers.')"
            :eyebrow="__('Shopping activity')">
            <x-slot:action><x-ui.button :href="route('products.index')" variant="outline">{{ __('Explore products') }}</x-ui.button></x-slot:action>
        </x-ui.page-header>

        @if ($reviews->isEmpty())
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No reviews yet')" :description="__('After trying a product, share your experience to help other shoppers choose with confidence.')">
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3z"/></svg>
                    </x-slot:icon>
                    <x-slot:action><x-ui.button :href="route('products.index')">{{ __('Find a product to review') }}</x-ui.button></x-slot:action>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <div class="grid items-start gap-5 lg:grid-cols-2">
                @foreach ($reviews as $review)
                    <x-ui.card>
                        <article>
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-600">{{ __('Product review') }}</p>
                                    <h2 class="mt-1 text-base font-bold text-slate-900">{{ $review->product?->name ?? __('Product unavailable') }}</h2>
                                    <p class="mt-1 text-xs text-slate-500">{{ $review->created_at->translatedFormat('d M Y') }}</p>
                                </div>
                                <x-ui.badge tone="accent">{{ $review->rating }}/5</x-ui.badge>
                            </div>

                            <div class="mt-4 flex items-center gap-2" role="img" aria-label="{{ __(':rating out of 5 stars', ['rating' => $review->rating]) }}">
                                <div class="flex gap-1" aria-hidden="true">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <svg class="h-5 w-5 {{ $star <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3z"/></svg>
                                    @endfor
                                </div>
                                <span class="text-xs font-semibold text-slate-500">{{ __(':rating out of 5', ['rating' => $review->rating]) }}</span>
                            </div>

                            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-600">{{ filled($review->comment) ? $review->comment : __('No written comment provided.') }}</p>

                            @if ($review->product)
                                <div class="mt-5 border-t border-slate-100 pt-4">
                                    <x-ui.button :href="route('products.show', $review->product)" variant="outline" size="sm">
                                        {{ __('View product') }}
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </x-ui.button>
                                </div>
                            @endif
                        </article>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="ui-pagination mt-6">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
