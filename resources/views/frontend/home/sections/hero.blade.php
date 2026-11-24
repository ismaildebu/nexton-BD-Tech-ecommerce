@if ($section['layout'] === 'mosaic')
    <section id="home-hero" class="homepage-section mb-12" data-layout="mosaic" aria-labelledby="home-hero-title">
        <div class="grid gap-4 lg:grid-cols-[1.75fr_1fr]">
            <div class="relative isolate min-h-[24rem] overflow-hidden rounded-[1.75rem] bg-ink-900 text-white shadow-lg shadow-ink-950/10 sm:min-h-[28rem]">
                @if ($section['image_url'])
                    <img src="{{ $section['image_url'] }}" alt="" loading="eager" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover">
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-ink-950/95 via-ink-950/65 to-ink-950/10"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950/75 via-transparent to-transparent"></div>
                <div class="relative flex min-h-[24rem] max-w-2xl flex-col justify-end p-6 sm:min-h-[28rem] sm:p-10 lg:p-12">
                    @if ($section['eyebrow'])
                        <p class="mb-3 inline-flex w-fit rounded-full bg-white/15 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] ring-1 ring-inset ring-white/20">{{ $section['eyebrow'] }}</p>
                    @endif
                    <h2 id="home-hero-title" class="text-3xl font-black leading-[1.08] tracking-tight sm:text-5xl">{{ $section['title'] }}</h2>
                    @if ($section['description'])
                        <p class="mt-4 max-w-xl text-sm leading-6 text-white/75">{{ $section['description'] }}</p>
                    @endif
                    @if ($section['button_label'])
                        <x-ui.button :href="route('products.index')" class="mt-6 w-fit bg-white text-ink-900 hover:bg-slate-100">
                            {{ $section['button_label'] }}
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <aside class="relative isolate flex min-h-64 flex-col justify-between overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 p-6 text-white shadow-lg shadow-brand-950/10 sm:p-8" aria-label="{{ __('Featured collection') }}">
                <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full border-[30px] border-white/10" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-12 h-64 w-64 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                <div class="relative">
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] ring-1 ring-inset ring-white/20">{{ __('Featured collection') }}</span>
                    <p class="mt-6 text-3xl font-black leading-tight sm:text-4xl">{{ __('Fresh finds, carefully curated.') }}</p>
                    <p class="mt-3 text-sm leading-6 text-white/80">{{ __('Explore a focused edit of products from approved merchants.') }}</p>
                </div>
                <div class="relative mt-8">
                    <div class="mb-5 flex items-center gap-2 text-xs font-bold text-white/85">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                        </span>
                        {{ __('Verified merchants only') }}
                    </div>
                    <x-ui.button :href="route('products.index')" variant="dark" class="w-full">
                        {{ __('Shop the collection') }}
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </x-ui.button>
                </div>
            </aside>
        </div>
    </section>
@elseif ($section['layout'] === 'campaign')
    <section id="home-hero" class="homepage-section mb-14" data-layout="campaign" aria-labelledby="home-hero-title">
        <div class="relative isolate flex min-h-[30rem] items-end overflow-hidden rounded-[2rem] bg-ink-900 text-white shadow-2xl shadow-ink-950/15 sm:min-h-[36rem]">
            @if ($section['image_url'])
                <img src="{{ $section['image_url'] }}" alt="" loading="eager" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/55 to-ink-950/10"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-ink-950/55 via-transparent to-transparent"></div>
            <p class="pointer-events-none absolute right-8 top-8 hidden text-[10px] font-bold uppercase tracking-[0.4em] text-white/60 [writing-mode:vertical-rl] sm:block" aria-hidden="true">{{ __('Curated marketplace') }}</p>
            <div class="relative max-w-4xl p-6 sm:p-12 lg:p-16">
                @if ($section['eyebrow'])
                    <p class="mb-5 inline-flex items-center gap-3 text-[11px] font-black uppercase tracking-[0.24em] text-white/75">
                        <span class="h-px w-10 bg-white/60" aria-hidden="true"></span>
                        {{ $section['eyebrow'] }}
                    </p>
                @endif
                <h2 id="home-hero-title" class="text-4xl font-black leading-[0.98] tracking-[-0.04em] sm:text-6xl lg:text-7xl">{{ $section['title'] }}</h2>
                @if ($section['description'])
                    <p class="mt-6 max-w-xl text-sm leading-7 text-white/75 sm:text-base">{{ $section['description'] }}</p>
                @endif
                @if ($section['button_label'])
                    <x-ui.button :href="route('products.index')" class="mt-8 w-fit bg-white text-ink-900 hover:bg-slate-100">
                        {{ $section['button_label'] }}
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </x-ui.button>
                @endif
            </div>
        </div>
    </section>
@else
<section id="home-hero" class="homepage-section mb-12" data-layout="{{ $section['layout'] }}" aria-labelledby="home-hero-title">
    <div class="relative isolate overflow-hidden rounded-[2rem] bg-ink-900 text-white shadow-2xl shadow-ink-950/10">
        @if ($section['image_url'])
            <img src="{{ $section['image_url'] }}" alt="" loading="eager" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover opacity-45">
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-ink-950 via-ink-900/85 to-ink-900/35"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-ink-950/70 via-transparent to-transparent"></div>
        <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full border border-white/10" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 rounded-full border border-white/10" aria-hidden="true"></div>

        <div @class([
            'relative grid min-h-[30rem] items-center gap-10 p-6 sm:p-10 lg:p-14',
            'lg:grid-cols-[1.1fr_0.9fr]' => $section['layout'] === 'split',
            'lg:grid-cols-1 lg:text-center' => $section['layout'] === 'centered',
        ])>
            <div @class(['max-w-2xl', 'lg:mx-0' => $section['layout'] === 'split', 'lg:mx-auto' => $section['layout'] === 'centered'])>
                @if ($section['eyebrow'])
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-brand-100 ring-1 ring-inset ring-white/15">
                        <span class="h-1.5 w-1.5 rounded-full bg-brand-300" aria-hidden="true"></span>
                        {{ $section['eyebrow'] }}
                    </p>
                @endif
                <h2 id="home-hero-title" class="text-3xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">{{ $section['title'] }}</h2>
                @if ($section['description'])
                    <p class="mt-5 max-w-xl text-sm leading-7 text-slate-200 sm:text-base">{{ $section['description'] }}</p>
                @endif
                <div @class(['mt-7 flex flex-wrap gap-3', 'lg:justify-center' => $section['layout'] === 'centered'])>
                    @if ($section['button_label'])
                        <x-ui.button :href="route('products.index')" variant="dark" class="bg-white text-ink-900 hover:bg-slate-100">
                            {{ $section['button_label'] }}
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </x-ui.button>
                    @endif
                    <x-ui.button :href="route('products.index')" variant="outline" class="border-white/25 bg-white/5 text-white hover:border-white/40 hover:bg-white/10">
                        {{ __('Browse catalog') }}
                    </x-ui.button>
                </div>
                <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-xs font-semibold text-slate-300">
                    <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>{{ __('Verified merchants') }}</span>
                    <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"/></svg>{{ __('Secure checkout') }}</span>
                    <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M4 7h16M4 17h10"/></svg>{{ __('Order tracking') }}</span>
                </div>
            </div>

            @if ($section['layout'] === 'split')
                <aside class="relative hidden lg:block" aria-label="{{ __('Marketplace highlights') }}">
                    <div class="ml-auto max-w-sm rounded-[1.75rem] border border-white/15 bg-white/10 p-6 shadow-2xl backdrop-blur-xl">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-200">{{ __('Marketplace highlights') }}</p>
                                <p class="mt-2 text-lg font-black">{{ __('Curated. Verified. Ready.') }}</p>
                            </div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-950/30">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                        </div>
                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-inset ring-white/10">
                                <p class="text-2xl font-black">{{ count($featuredProducts) }}</p>
                                <p class="mt-1 text-[11px] text-slate-300">{{ __('Curated products') }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-inset ring-white/10">
                                <p class="text-2xl font-black">{{ count($featuredVendors) }}</p>
                                <p class="mt-1 text-[11px] text-slate-300">{{ __('Verified merchants') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-3 rounded-2xl bg-ink-950/35 p-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-300">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                            </span>
                            <p class="text-xs leading-5 text-slate-200">{{ __('Every featured product comes from an approved, active seller.') }}</p>
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    </div>
</section>
@endif
