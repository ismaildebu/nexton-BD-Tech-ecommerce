@php
    $benefits = [
        ['title' => __('Verified merchants'), 'description' => __('Shop from approved sellers with active storefronts.'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/>'],
        ['title' => __('Secure checkout'), 'description' => __('Clear cart totals and protected form submissions.'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"/>'],
        ['title' => __('Order tracking'), 'description' => __('Follow order status from your customer dashboard.'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M4 7h16M4 17h10"/>'],
        ['title' => __('Dedicated support'), 'description' => __('Contact information and updates stay easy to find.'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 13a8 8 0 0 1 16 0M4 13v5h4v-5H4Zm16 0v5h-4v-5h4Zm-12 5h4"/>'],
    ];
@endphp

<section id="home-benefits" class="homepage-section mb-6" data-layout="{{ $section['layout'] }}" aria-labelledby="home-benefits-title">
    <div @class([
        'relative overflow-hidden rounded-[2rem] border border-slate-200/70 bg-white p-6 shadow-sm sm:p-10' => $section['layout'] !== 'minimal',
        'border-t border-slate-200 pt-8' => $section['layout'] === 'minimal',
    ])>
        @if ($section['layout'] !== 'minimal')
            <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-brand-100/50 blur-3xl" aria-hidden="true"></div>
        @endif
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                @if ($section['eyebrow'])
                    <p class="ui-eyebrow mb-2">{{ $section['eyebrow'] }}</p>
                @endif
                <h2 id="home-benefits-title" class="text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">{{ $section['title'] }}</h2>
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

        <div @class([
            'relative mt-8 grid gap-4',
            'sm:grid-cols-2 lg:grid-cols-4' => in_array($section['layout'], ['cards', 'strip', 'minimal'], true),
        ])>
            @foreach ($benefits as $index => $benefit)
                <div @class([
                    'benefit-item flex gap-4 rounded-2xl',
                    'border border-slate-100 bg-slate-50/70 p-5' => $section['layout'] === 'cards',
                    'border-transparent px-2 py-4' => $section['layout'] === 'strip',
                    'border-transparent px-1 py-2 sm:pl-5' => $section['layout'] === 'minimal',
                    'sm:border-l sm:border-slate-200' => $section['layout'] === 'minimal' && ! $loop->first,
                ])>
                    <span @class([
                        'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
                        ($index % 2 === 0 ? 'bg-brand-50 text-brand-600' : 'bg-accent-50 text-accent-600') => $section['layout'] !== 'minimal',
                        'bg-transparent text-ink-900' => $section['layout'] === 'minimal',
                    ])>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $benefit['icon'] !!}</svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">{{ $benefit['title'] }}</h3>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $benefit['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
