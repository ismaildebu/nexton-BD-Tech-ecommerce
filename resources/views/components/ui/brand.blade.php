@props(['inverse' => false, 'compact' => false, 'href' => null])

<a href="{{ $href ?? url('/') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20']) }} aria-label="{{ config('app.name') }}">
    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $inverse ? 'bg-white/10 text-white ring-1 ring-white/15' : 'bg-ink-900 text-white' }}">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    </span>
    <span>
        <span class="block text-lg font-black leading-none tracking-tight {{ $inverse ? 'text-white' : 'text-ink-900' }}">NEXT<span class="text-brand-500">ON</span></span>
        @unless ($compact)
            <span class="mt-1 block text-[9px] font-bold uppercase leading-none tracking-[0.2em] {{ $inverse ? 'text-slate-400' : 'text-slate-500' }}">Commerce OS</span>
        @endunless
    </span>
</a>
