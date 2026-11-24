@props(['status' => null])

@php
    $value = strtolower((string) ($status ?? $slot));
    $tone = match (true) {
        in_array($value, ['paid', 'active', 'approved', 'completed', 'delivered', 'published', 'success']) => 'success',
        in_array($value, ['pending', 'processing', 'shipped', 'invited', 'partially_paid', 'partially paid', 'draft', 'under_review', 'under review']) => 'warning',
        in_array($value, ['failed', 'cancelled', 'canceled', 'rejected', 'revoked', 'expired', 'suspended', 'inactive', 'refunded', 'unpaid', 'out_of_stock', 'out of stock']) => 'danger',
        in_array($value, ['refunded_partially', 'partially_refunded', 'partially refunded']) => 'accent',
        default => 'neutral',
    };
@endphp

<x-ui.badge :tone="$tone" {{ $attributes }}>{{ __(str_replace('_', ' ', (string) ($status ?? $slot))) }}</x-ui.badge>
