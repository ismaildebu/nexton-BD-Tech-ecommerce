@props(['variant' => 'primary', 'size' => null, 'href' => null, 'type' => 'button'])

@php
    $variants = [
        'primary' => 'ui-btn-primary',
        'secondary' => 'ui-btn-secondary',
        'dark' => 'ui-btn-dark',
        'outline' => 'ui-btn-outline',
        'ghost' => 'ui-btn-ghost',
        'danger' => 'ui-btn-danger',
        'success' => 'ui-btn-success',
    ];
    $classes = 'ui-btn '.($variants[$variant] ?? $variants['primary']).($size === 'sm' ? ' ui-btn-sm' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
