@props(['inverse' => false, 'compact' => false, 'href' => null])

<a href="{{ $href ?? url('/') }}" {{ $attributes->merge(['class' => 'inline-flex min-w-0 items-center gap-3 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20']) }} aria-label="{{ $siteSettings->site_name }}">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl p-1.5 {{ $inverse ? 'bg-white/10 text-white ring-1 ring-white/15' : 'bg-ink-900 text-white' }}">
        @if ($siteSettings->logo_url)
            <img src="{{ $siteSettings->logo_url }}" alt="" class="h-full w-full object-contain">
        @else
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        @endif
    </span>
    <span class="min-w-0">
        <span class="block max-w-[10rem] truncate text-lg font-black leading-none tracking-tight {{ $inverse ? 'text-white' : 'text-ink-900' }}">{{ $siteSettings->site_name }}</span>
        @unless ($compact)
            <span class="mt-1 block text-[9px] font-bold uppercase leading-none tracking-[0.2em] {{ $inverse ? 'text-slate-400' : 'text-slate-500' }}">Commerce OS</span>
        @endunless
    </span>
</a>
