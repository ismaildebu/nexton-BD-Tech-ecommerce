@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'ui-card'.($padding ? ' p-5 sm:p-6' : '')]) }}>{{ $slot }}</div>
