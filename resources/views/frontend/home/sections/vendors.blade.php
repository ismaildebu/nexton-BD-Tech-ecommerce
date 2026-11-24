<section id="vendors" class="homepage-section mb-16" data-layout="{{ $section['layout'] }}" aria-labelledby="vendors-title">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            @if ($section['eyebrow'])
                <p class="ui-eyebrow mb-2">{{ $section['eyebrow'] }}</p>
            @endif
            <h2 id="vendors-title" class="text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">{{ $section['title'] }}</h2>
            @if ($section['description'])
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $section['description'] }}</p>
            @endif
        </div>
        @if ($section['button_label'])
            <x-ui.button :href="route('vendor.register.create')" variant="outline" size="sm">
                {{ $section['button_label'] }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </x-ui.button>
        @endif
    </div>

    @if ($section['layout'] === 'logos')
        <div class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-brand-50/50 px-5 py-8 sm:px-8 sm:py-10">
            <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full border-[40px] border-brand-100/40" aria-hidden="true"></div>
            <div class="relative grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 sm:grid-cols-3">
                @forelse ($featuredVendors as $vendor)
                    <div class="flex min-h-28 flex-col items-center justify-center gap-2 bg-white/95 px-4 py-5 text-center">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-ink-900 text-sm font-black text-white">{{ substr($vendor->business_name, 0, 1) }}</span>
                        <span class="line-clamp-2 break-words text-sm font-black tracking-tight text-slate-800">{{ $vendor->business_name }}</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ __('Verified') }}
                        </span>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-8 text-center text-sm text-slate-500">{{ __('No verified vendors yet') }}</div>
                @endforelse
            </div>
        </div>
    @else
    <div @class([
        'grid gap-4',
        'sm:grid-cols-2 lg:grid-cols-3' => $section['layout'] === 'grid',
        'lg:grid-cols-2' => $section['layout'] === 'list',
    ])>
        @forelse ($featuredVendors as $vendor)
            <x-ui.card class="group transition hover:border-accent-200 hover:shadow-md {{ $section['layout'] === 'list' ? 'p-5' : 'p-6' }}">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-accent-50 to-brand-50 text-xl font-black text-accent-700 ring-1 ring-inset ring-accent-100" aria-hidden="true">{{ substr($vendor->business_name, 0, 1) }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-1.5">
                            <h3 class="break-words text-sm font-bold text-slate-800">{{ $vendor->business_name }}</h3>
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span class="sr-only">{{ __('Verified vendor') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $vendor->district ?? __('Dhaka, Bangladesh') }}</p>
                        <p class="mt-2 text-[11px] font-semibold text-accent-700">{{ __('Code') }}: {{ $vendor->vendor_code }}</p>
                    </div>
                    @if ($section['layout'] === 'list')
                        <x-ui.badge tone="success" class="hidden sm:inline-flex">{{ __('Verified') }}</x-ui.badge>
                    @endif
                </div>
            </x-ui.card>
        @empty
            <x-ui.card :padding="false" class="col-span-full">
                <x-ui.empty-state :title="__('No verified vendors yet')" :description="__('Verified merchant stores will appear here once they join.')" />
            </x-ui.card>
        @endforelse
    </div>
    @endif
</section>
