@props(['tone' => 'info', 'title' => null])

@php
    $styles = match ($tone) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'error', 'danger' => 'border-rose-200 bg-rose-50 text-rose-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        default => 'border-sky-200 bg-sky-50 text-sky-900',
    };
    $icon = match ($tone) {
        'success' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
        'error', 'danger' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
        'warning' => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.35 2.722-1.35 3.486 0l5.455 9.594c.75 1.32-.213 2.97-1.742 2.97H3.544c-1.53 0-2.492-1.65-1.743-2.97L7.257 3.1zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V7a1 1 0 00-1-1z" clip-rule="evenodd"/>',
        default => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0v5a1 1 0 102 0V6zm-1 8a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>',
    };
@endphp

<div {{ $attributes->merge(['role' => in_array($tone, ['error', 'danger']) ? 'alert' : 'status', 'class' => "flex gap-3 rounded-2xl border p-4 text-sm shadow-sm $styles"]) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">{!! $icon !!}</svg>
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-bold">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
    </div>
</div>
