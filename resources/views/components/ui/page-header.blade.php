@props(['title', 'description' => null, 'eyebrow' => null])

<header {{ $attributes->merge(['class' => 'ui-page-header']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="ui-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="ui-page-title break-words [overflow-wrap:anywhere]">{{ $title }}</h1>
        @if ($description)
            <p class="ui-page-description break-words [overflow-wrap:anywhere]">{{ $description }}</p>
        @endif
    </div>
    @if (isset($action))
        <div class="flex shrink-0 flex-wrap items-center gap-3">{{ $action }}</div>
    @endif
</header>
