@props(['caption' => null])

<div {{ $attributes->merge(['class' => 'ui-table-wrap']) }}>
    <div class="ui-table-scroll ui-scrollbar focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500" tabindex="0" role="region" aria-label="{{ $caption ?? __('Scrollable table') }}">
        <table class="ui-table">
            @if ($caption)
                <caption class="sr-only">{{ $caption }}</caption>
            @endif
            @if (isset($head))
                <thead>{{ $head }}</thead>
            @endif
            <tbody>{{ $slot }}</tbody>
        </table>
    </div>
</div>
