@php
    $categoryIcons = [
        '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17V9m4 8V5m4 12v-6M4 17h16"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v12H4zM8 21h8M12 17v4"/>',
        '<rect x="7" y="2" width="10" height="20" rx="2"/><path stroke-linecap="round" d="M11 18h2"/>',
        '<circle cx="12" cy="12" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 3-3 5 5M3 21h18"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M6 12V7h12v5M6 12v7h12v-7M9 19v2h6v-2"/>',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h4l2-2h4l2 2h4v13H4z"/><circle cx="12" cy="13" r="4"/>',
    ];
@endphp

<section id="home-categories" class="homepage-section mb-14" data-layout="{{ $section['layout'] }}" aria-labelledby="home-categories-title">
    <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            @if ($section['eyebrow'])
                <p class="ui-eyebrow mb-2">{{ $section['eyebrow'] }}</p>
            @endif
            <h2 id="home-categories-title" class="text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">{{ $section['title'] }}</h2>
            @if ($section['description'])
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $section['description'] }}</p>
            @endif
        </div>
        @if ($section['button_label'])
            <x-ui.button :href="route('products.index')" variant="ghost" size="sm">
                {{ $section['button_label'] }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </x-ui.button>
        @endif
    </div>

    @if ($section['layout'] === 'rail')
        <div class="relative" x-data="{ scrollCategories(direction) { $refs.categoryRail.scrollBy({ left: direction * 360, behavior: 'smooth' }); } }">
            <nav x-ref="categoryRail" class="homepage-scroll flex snap-x snap-mandatory gap-4 overflow-x-auto px-1 pb-3 sm:gap-6" aria-label="{{ __('Shop by category') }}">
                <a href="{{ route('products.index') }}" class="group flex w-24 shrink-0 snap-start flex-col items-center gap-3 rounded-2xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 sm:w-28">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-brand-200 bg-brand-50 text-brand-600 shadow-sm transition group-hover:border-brand-500 group-hover:bg-brand-100 sm:h-24 sm:w-24">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
                    </span>
                    <span class="text-center text-xs font-bold text-slate-700">{{ __('All products') }}</span>
                </a>
                @foreach ($categories as $index => $category)
                    <a href="{{ route('products.index', ['category' => $category->id]) }}" class="group flex w-24 shrink-0 snap-start flex-col items-center gap-3 rounded-2xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 sm:w-28">
                        <span class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-slate-100 bg-white text-accent-600 shadow-sm transition group-hover:border-accent-300 group-hover:bg-accent-50 sm:h-24 sm:w-24">
                            <svg class="h-8 w-8 transition group-hover:scale-105" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">{!! $categoryIcons[$index % count($categoryIcons)] !!}</svg>
                        </span>
                        <span class="line-clamp-2 text-center text-xs font-semibold leading-4 text-slate-600">{{ $category->name }}</span>
                    </a>
                @endforeach
            </nav>
            <button type="button" @click="scrollCategories(-1)" class="absolute -left-3 top-8 hidden h-10 w-10 items-center justify-center rounded-full border border-brand-100 bg-white text-brand-600 shadow-md transition hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 sm:flex" aria-label="{{ __('Previous categories') }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" @click="scrollCategories(1)" class="absolute -right-3 top-8 hidden h-10 w-10 items-center justify-center rounded-full bg-brand-500 text-white shadow-md transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 sm:flex" aria-label="{{ __('Next categories') }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    @elseif ($section['layout'] === 'tiles')
        <div class="grid auto-rows-[12rem] grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            <a href="{{ route('products.index') }}" class="group relative row-span-2 flex flex-col justify-between overflow-hidden rounded-[1.5rem] bg-ink-900 p-5 text-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 sm:col-span-2 sm:p-7">
                <span class="relative z-10 text-[10px] font-black uppercase tracking-[0.2em] text-white/60">{{ __('The complete edit') }}</span>
                <span class="relative z-10">
                    <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-full border border-white/20 text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
                    </span>
                    <span class="block text-2xl font-black">{{ __('All products') }}</span>
                    <span class="mt-2 block text-xs text-white/60">{{ __('Explore every collection') }}</span>
                </span>
                <span class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full border-[28px] border-white/5" aria-hidden="true"></span>
            </a>
            @foreach ($categories as $index => $category)
                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="group relative flex flex-col justify-between overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-accent-200 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full {{ $index % 2 === 0 ? 'bg-accent-50 text-accent-600' : 'bg-brand-50 text-brand-600' }}">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">{!! $categoryIcons[$index % count($categoryIcons)] !!}</svg>
                    </span>
                    <span>
                        <span class="block break-words text-sm font-extrabold text-slate-800">{{ $category->name }}</span>
                        <span class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-400 transition group-hover:text-brand-600">{{ __('Explore') }}<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                    </span>
                </a>
            @endforeach
        </div>
    @else
    @if ($section['layout'] === 'cards')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            <a href="{{ route('products.index') }}" class="category-tile group relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-br from-brand-50 to-white p-4 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-500 text-white shadow-md shadow-brand-500/20 transition group-hover:scale-105">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
                </span>
                <span class="mt-3 block truncate text-xs font-bold text-slate-700">{{ __('All products') }}</span>
            </a>
            @foreach ($categories as $index => $category)
                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="category-tile group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-4 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:border-accent-100 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl {{ $index % 2 === 0 ? 'bg-accent-50 text-accent-600' : 'bg-slate-100 text-slate-600' }} transition group-hover:scale-105">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">{!! $categoryIcons[$index % count($categoryIcons)] !!}</svg>
                    </span>
                    <span class="mt-3 block truncate text-xs font-bold text-slate-700" title="{{ $category->name }}">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    @else
        <nav class="ui-scrollbar flex gap-5 overflow-x-auto pb-3" aria-label="{{ __('Shop by category') }}">
            <a href="{{ route('products.index') }}" class="group flex w-24 shrink-0 flex-col items-center gap-3 rounded-2xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                <span class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-brand-200 bg-brand-50 text-brand-600 shadow-sm transition group-hover:border-brand-500 group-hover:bg-brand-100">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
                </span>
                <span class="text-center text-xs font-bold text-slate-600">{{ __('All products') }}</span>
            </a>
            @foreach ($categories as $index => $category)
                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="group flex w-24 shrink-0 flex-col items-center gap-3 rounded-2xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-slate-100 bg-white text-accent-600 shadow-sm transition group-hover:border-accent-300 group-hover:bg-accent-50">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">{!! $categoryIcons[$index % count($categoryIcons)] !!}</svg>
                    </span>
                    <span class="line-clamp-2 text-center text-xs font-semibold leading-4 text-slate-600">{{ $category->name }}</span>
                </a>
            @endforeach
        </nav>
    @endif
    @endif
</section>
