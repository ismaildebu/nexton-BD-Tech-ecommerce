@props(['tone' => 'neutral'])

@php
    $styles = match ($tone) {
        'success' => 'ui-badge-success',
        'warning' => 'ui-badge-warning',
        'danger', 'error' => 'ui-badge-danger',
        'info' => 'ui-badge-info',
        'accent' => 'ui-badge-accent',
        default => 'ui-badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => "ui-badge $styles"]) }}>{{ $slot }}</span>
