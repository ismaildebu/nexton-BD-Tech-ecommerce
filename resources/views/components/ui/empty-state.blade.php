@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'ui-empty']) }}>
    <div class="ui-empty-icon">
        @if (isset($icon))
            {{ $icon }}
        @else
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        @endif
    </div>
    <h2 class="text-lg font-bold text-slate-900">{{ $title }}</h2>
    @if ($description)
        <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-6 flex flex-wrap justify-center gap-3">{{ $action }}</div>
    @endif
</div>
