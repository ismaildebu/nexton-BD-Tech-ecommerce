@php
    $productIcons = [
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v11H4zM8 20h8M12 16v4"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 14v-2a8 8 0 0116 0v2M4 13H3v6h4v-6H4zm16 0h1v6h-4v-6h3z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10a3 3 0 013 3v5a3 3 0 01-3 3H7a3 3 0 01-3-3v-5a3 3 0 013-3zM8 10v5m-2-2.5h4M16 11h.01M18 13h.01"/>',
        '<circle cx="12" cy="12" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>',
        '<rect x="7" y="2" width="10" height="20" rx="2"/><path stroke-linecap="round" d="M11 18h2"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h4l2-2h4l2 2h4v13H4z"/><circle cx="12" cy="13" r="4"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 3-3 5 5M3 21h18"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M6 12V7h12v5M6 12v7h12v-7M9 19v2h6v-2"/>',
    ];
@endphp

<section id="popular" class="homepage-section mb-16" data-layout="{{ $section['layout'] }}" aria-labelledby="popular-title">
    <div class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            @if ($section['eyebrow'])
                <p class="ui-eyebrow mb-2">{{ $section['eyebrow'] }}</p>
            @endif
            <h2 id="popular-title" class="text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">{{ $section['title'] }}</h2>
            @if ($section['description'])
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $section['description'] }}</p>
            @endif
        </div>
        @if ($section['button_label'])
            <x-ui.button :href="route('products.index')" variant="outline" size="sm">
                {{ $section['button_label'] }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </x-ui.button>
        @endif
    </div>

    @if ($section['layout'] === 'shelf')
        <div class="homepage-scroll -mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            @forelse ($featuredProducts as $product)
                @php($stock = (int) ($product->inventory?->quantity ?? 0))
                <x-ui.card :padding="false" class="group w-[16.5rem] shrink-0 snap-start overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-lg sm:w-[18rem]">
                    <a href="{{ route('products.show', $product) }}" class="relative flex h-48 items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 to-brand-50/60 p-6 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-brand-500/30" aria-label="{{ $product->name }}">
                        @if ($loop->first || $loop->iteration % 3 === 0)
                            <span class="absolute left-4 top-4 rounded-full {{ $loop->first ? 'bg-rose-500' : 'bg-brand-500' }} px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.12em] text-white shadow-sm">{{ $loop->first ? __('Best selling') : __('New arrival') }}</span>
                        @endif
                        <svg class="h-20 w-20 text-accent-500 transition duration-300 group-hover:scale-105" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1" aria-hidden="true">{!! $productIcons[$loop->index % count($productIcons)] !!}</svg>
                    </a>
                    <div class="p-4">
                        <p class="text-xs font-semibold text-slate-400">{{ $product->brand->name ?? ($product->category->name ?? $siteSettings->site_name) }}</p>
                        <a href="{{ route('products.show', $product) }}" class="mt-1.5 block rounded-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                            <h3 class="line-clamp-2 min-h-12 break-words text-sm font-bold leading-6 text-slate-800">{{ $product->name }}</h3>
                        </a>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <p class="text-lg font-black text-ink-900">৳ {{ number_format($product->price, 2) }}</p>
                            <x-ui.badge :tone="$stock > 0 ? 'success' : 'danger'">{{ $stock > 0 ? __('In stock') : __('Out of stock') }}</x-ui.badge>
                        </div>
                        <form method="POST" action="{{ route('cart.store') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <x-ui.button type="submit" variant="outline" class="w-full border-brand-200 text-brand-700 hover:border-brand-300 hover:bg-brand-50" :disabled="$stock < 1">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 12.2a2 2 0 002 1.6h7.7a2 2 0 002-1.6L21 7H6"/></svg>
                                {{ __('Add to cart') }}
                            </x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card :padding="false" class="w-full">
                    <x-ui.empty-state :title="__('No products available yet')" :description="__('Approved products from verified merchants will appear here.')" />
                </x-ui.card>
            @endforelse
        </div>
    @else
        <div class="ui-scrollbar mb-6 flex items-center gap-2 overflow-x-auto pb-2">
        <a href="{{ route('products.index') }}" class="inline-flex min-h-11 shrink-0 items-center whitespace-nowrap rounded-full bg-ink-900 px-4 text-xs font-bold text-white transition hover:bg-ink-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">{{ __('All') }}</a>
        @foreach ($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->id]) }}" class="inline-flex min-h-11 shrink-0 items-center whitespace-nowrap rounded-full border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">{{ $category->name }}</a>
        @endforeach
    </div>

    <div @class([
        'grid grid-cols-1 gap-4 min-[420px]:grid-cols-2 sm:gap-6',
        'lg:grid-cols-4' => in_array($section['layout'], ['grid', 'compact'], true),
        'lg:grid-cols-3' => $section['layout'] === 'editorial',
    ])>
        @forelse ($featuredProducts as $product)
            @php($stock = (int) ($product->inventory?->quantity ?? 0))
            @php($available = $stock > 0)
            @php($editorialLead = $section['layout'] === 'editorial' && $loop->first)
            <x-ui.card
                :padding="false"
                class="homepage-product-card group relative h-full overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-lg {{ $editorialLead ? 'lg:col-span-2' : '' }}"
            >
                <div class="flex h-full flex-col">
                    <form method="POST" action="{{ route('wishlist.store') }}" class="absolute right-4 top-4 z-10">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="ui-icon-btn border border-slate-200 bg-white/95 shadow-sm hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500" aria-label="{{ __('Save to wishlist') }}" title="{{ __('Save to wishlist') }}">
                            <svg class="h-5 w-5 transition {{ in_array($product->id, $wishlistedProductIds, true) ? 'fill-rose-500 text-rose-500' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </button>
                    </form>

                    <a href="{{ route('products.show', $product) }}" class="block rounded-2xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-brand-500/30" aria-label="{{ $product->name }}">
                        <div @class([
                            'flex items-center justify-center rounded-2xl bg-gradient-to-br from-slate-50 to-accent-50/60 text-accent-500 transition duration-300 group-hover:from-accent-50 group-hover:to-brand-50',
                            'h-36 sm:h-44' => $section['layout'] === 'compact',
                            'h-44 sm:h-52' => ! $editorialLead && $section['layout'] !== 'compact',
                            'h-56 sm:h-72' => $editorialLead,
                        ])>
                            <svg @class(['transition duration-300 group-hover:scale-105', 'h-16 w-16 sm:h-20 sm:w-20' => ! $editorialLead, 'h-24 w-24 sm:h-28 sm:w-28' => $editorialLead]) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">{!! $productIcons[$loop->index % count($productIcons)] !!}</svg>
                        </div>
                    </a>

                    <div class="flex flex-1 flex-col p-4 sm:p-5">
                        <p class="mb-1 text-base font-extrabold text-slate-900 sm:text-lg">৳ {{ number_format($product->price, 2) }}</p>
                        <a href="{{ route('products.show', $product) }}" class="rounded-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                            <h3 class="break-words text-sm font-bold leading-6 text-slate-800 transition group-hover:text-brand-700">{{ $product->name }}</h3>
                        </a>
                        <p class="mt-1 text-xs text-slate-500">{{ $product->brand->name ?? ($product->category->name ?? __(':store Tech', ['store' => $siteSettings->site_name])) }}</p>

                        <div class="mt-auto flex flex-col gap-3 border-t border-slate-100 pt-4">
                            <x-ui.badge :tone="$available ? 'success' : 'danger'">{{ $available ? __('In stock') : __('Out of stock') }}</x-ui.badge>
                            <form method="POST" action="{{ route('cart.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <x-ui.button type="submit" :disabled="! $available" class="w-full">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('Add to cart') }}
                                </x-ui.button>
                            </form>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card :padding="false" class="col-span-full">
                <x-ui.empty-state :title="__('No products available yet')" :description="__('Approved products from verified merchants will appear here.')">
                    <x-slot:action>
                        <x-ui.button :href="route('products.index')" variant="outline">{{ __('Browse catalog') }}</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </x-ui.card>
        @endforelse
    </div>
    @endif
</section>
