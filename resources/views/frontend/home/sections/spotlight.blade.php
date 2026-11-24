@if ($section['layout'] === 'bestsellers')
    <section id="home-spotlight" class="homepage-section mb-14" data-layout="bestsellers" aria-labelledby="home-spotlight-title">
        <div class="mb-7 text-center">
            @if ($section['eyebrow'])
                <p class="ui-eyebrow mb-2">{{ $section['eyebrow'] }}</p>
            @endif
            <h2 id="home-spotlight-title" class="text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">{{ $section['title'] }}</h2>
            @if ($section['description'])
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $section['description'] }}</p>
            @endif
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            @forelse ($featuredProducts->take(2) as $product)
                @php($stock = (int) ($product->inventory?->quantity ?? 0))
                <x-ui.card :padding="false" class="group overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="grid min-h-64 sm:grid-cols-[0.9fr_1.1fr]">
                        <a href="{{ route('products.show', $product) }}" class="relative flex items-center justify-center overflow-hidden bg-gradient-to-br from-brand-50 via-white to-accent-50 p-6 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-brand-500/30" aria-label="{{ $product->name }}">
                            <span class="absolute left-4 top-4 rounded-full {{ $loop->first ? 'bg-rose-500 text-white' : 'bg-brand-500 text-white' }} px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.12em] shadow-sm">{{ $loop->first ? __('Best selling') : __('New arrival') }}</span>
                            <svg class="h-24 w-24 text-accent-500 transition duration-300 group-hover:scale-105 sm:h-28 sm:w-28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </a>
                        <div class="flex flex-col p-5 sm:p-6">
                            <p class="text-xs font-semibold text-slate-400">{{ $product->brand->name ?? ($product->category->name ?? $siteSettings->site_name) }}</p>
                            <a href="{{ route('products.show', $product) }}" class="mt-2 rounded-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                                <h3 class="break-words text-lg font-black leading-6 text-slate-900">{{ $product->name }}</h3>
                            </a>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="text-xl font-black text-brand-700">৳ {{ number_format($product->price, 2) }}</span>
                                <x-ui.badge :tone="$stock > 0 ? 'success' : 'danger'">{{ $stock > 0 ? __('In stock') : __('Out of stock') }}</x-ui.badge>
                            </div>
                            <div class="mt-auto flex flex-wrap gap-2 pt-5">
                                <form method="POST" action="{{ route('cart.store') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <x-ui.button type="submit" variant="outline" size="sm" :disabled="$stock < 1">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 12.2a2 2 0 002 1.6h7.7a2 2 0 002-1.6L21 7H6"/></svg>
                                        {{ __('Add to cart') }}
                                    </x-ui.button>
                                </form>
                                <x-ui.button :href="route('products.show', $product)" size="sm">{{ __('View details') }}</x-ui.button>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card :padding="false" class="col-span-full">
                    <x-ui.empty-state :title="__('No products available yet')" :description="__('Approved products will appear here.')" />
                </x-ui.card>
            @endforelse
        </div>
    </section>
@elseif ($section['layout'] === 'story')
    @if ($spotlightProduct)
        @php($stock = (int) ($spotlightProduct->inventory?->quantity ?? 0))
        <section id="home-spotlight" class="homepage-section mb-14" data-layout="story" aria-labelledby="home-spotlight-title">
            <div class="relative isolate overflow-hidden rounded-[2rem] bg-stone-100 text-stone-900">
                <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full border-[60px] border-white/60" aria-hidden="true"></div>
                <div class="relative grid items-center gap-8 p-6 sm:p-10 lg:grid-cols-[1.1fr_0.9fr] lg:p-14">
                    <div>
                        @if ($section['eyebrow'])
                            <p class="mb-4 text-[10px] font-black uppercase tracking-[0.24em] text-stone-500">{{ $section['eyebrow'] }}</p>
                        @endif
                        <h2 id="home-spotlight-title" class="text-3xl font-black leading-[1.02] tracking-[-0.035em] sm:text-5xl">{{ $section['title'] }}</h2>
                        @if ($section['description'])
                            <p class="mt-4 max-w-xl text-sm leading-6 text-stone-600">{{ $section['description'] }}</p>
                        @endif
                        <div class="mt-8 border-t border-stone-300 pt-6">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ __('The featured product') }}</p>
                            <a href="{{ route('products.show', $spotlightProduct) }}" class="mt-2 block rounded-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                                <h3 class="text-xl font-black sm:text-2xl">{{ $spotlightProduct->name }}</h3>
                            </a>
                            <p class="mt-2 text-sm text-stone-500">{{ $spotlightProduct->vendor->business_name }}</p>
                            <p class="mt-4 text-2xl font-black">৳ {{ number_format($spotlightProduct->price, 2) }}</p>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($section['button_label'])
                                <x-ui.button :href="route('products.show', $spotlightProduct)" variant="dark">{{ $section['button_label'] }}</x-ui.button>
                            @endif
                            <form method="POST" action="{{ route('cart.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $spotlightProduct->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <x-ui.button type="submit" variant="outline" :disabled="$stock < 1">{{ __('Add to cart') }}</x-ui.button>
                            </form>
                        </div>
                    </div>
                    <a href="{{ route('products.show', $spotlightProduct) }}" class="relative flex min-h-72 items-center justify-center overflow-hidden rounded-[1.5rem] bg-white shadow-inner focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20" aria-label="{{ $spotlightProduct->name }}">
                        <span class="absolute left-5 top-5 rounded-full bg-stone-900 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-white">{{ __('Editor’s selection') }}</span>
                        <svg class="h-36 w-36 text-stone-400 sm:h-44 sm:w-44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </a>
                </div>
            </div>
        </section>
    @endif
@elseif ($spotlightProduct)
    @php($spotlightStock = (int) ($spotlightProduct->inventory?->quantity ?? 0))

    <section id="home-spotlight" class="homepage-section mb-14" data-layout="{{ $section['layout'] }}" aria-labelledby="home-spotlight-title">
        <div @class([
            'relative overflow-hidden rounded-[2rem] border border-accent-100 bg-gradient-to-br from-accent-50 via-white to-brand-50 shadow-sm',
            'p-6 sm:p-10' => $section['layout'] === 'horizontal',
            'p-6 sm:p-12' => $section['layout'] === 'editorial',
        ])>
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-accent-200/30 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-brand-200/25 blur-3xl" aria-hidden="true"></div>

            <div @class([
                'relative grid items-center gap-8',
                'lg:grid-cols-[0.85fr_1.15fr]' => $section['layout'] === 'horizontal',
                'lg:grid-cols-[0.8fr_1.2fr]' => $section['layout'] === 'editorial',
            ])>
                <div class="relative">
                    <div class="absolute inset-8 rounded-full bg-white/70 blur-2xl" aria-hidden="true"></div>
                    <a href="{{ route('products.show', $spotlightProduct) }}" class="relative flex min-h-64 items-center justify-center rounded-[1.5rem] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20" aria-label="{{ $spotlightProduct->name }}">
                        <span class="absolute left-5 top-5 rounded-full bg-white/80 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-accent-700 shadow-sm backdrop-blur">{{ __('Featured find') }}</span>
                        <svg class="h-32 w-32 text-accent-500 sm:h-40 sm:w-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </a>
                </div>

                <div>
                    @if ($section['eyebrow'])
                        <p class="ui-eyebrow mb-3">{{ $section['eyebrow'] }}</p>
                    @endif
                    <h2 id="home-spotlight-title" class="text-2xl font-extrabold tracking-tight text-ink-900 sm:text-4xl">{{ $section['title'] }}</h2>
                    @if ($section['description'])
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-500">{{ $section['description'] }}</p>
                    @endif

                    <div class="mt-7 border-y border-slate-200/70 py-5">
                        <a href="{{ route('products.show', $spotlightProduct) }}" class="rounded-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                            <h3 class="break-words text-xl font-black text-slate-900 sm:text-2xl">{{ $spotlightProduct->name }}</h3>
                        </a>
                        <p class="mt-1 text-sm text-slate-500">{{ $spotlightProduct->vendor->business_name }}</p>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <x-ui.badge :tone="$spotlightStock > 0 ? 'success' : 'danger'">{{ $spotlightStock > 0 ? __('In stock') : __('Out of stock') }}</x-ui.badge>
                            <span class="text-lg font-black text-ink-900">৳ {{ number_format($spotlightProduct->price, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        @if ($section['button_label'])
                            <x-ui.button :href="route('products.show', $spotlightProduct)" variant="primary">
                                {{ $section['button_label'] }}
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                            </x-ui.button>
                        @endif
                        <form method="POST" action="{{ route('cart.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $spotlightProduct->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <x-ui.button type="submit" variant="dark" :disabled="$spotlightStock < 1">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                {{ __('Add to cart') }}
                            </x-ui.button>
                        </form>
                        <form method="POST" action="{{ route('wishlist.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $spotlightProduct->id }}">
                            <button type="submit" class="ui-icon-btn border border-slate-200 bg-white shadow-sm hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500" aria-label="{{ __('Save to wishlist') }}" title="{{ __('Save to wishlist') }}">
                                <svg class="h-5 w-5 transition {{ in_array($spotlightProduct->id, $wishlistedProductIds, true) ? 'fill-rose-500 text-rose-500' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
