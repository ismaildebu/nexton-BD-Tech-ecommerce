@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'required' => false,
    'hint' => null,
])

@php
    $id = $attributes->get('id', $name);
    $describedBy = trim(($hint ? "$id-help" : '').' '.($errors->has($name) ? "$id-error" : ''));
@endphp

<div>
    <label for="{{ $id }}" class="ui-label">{{ $label }} @if ($required)<span class="text-brand-600" aria-hidden="true">*</span>@endif</label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @required($required)
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @error($name) aria-invalid="true" @enderror
        {{ $attributes->except('id')->merge(['class' => 'ui-input']) }}
    >
    @if ($hint)
        <p id="{{ $id }}-help" class="ui-help">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $id }}-error" class="ui-error">{{ $message }}</p>
    @enderror
</div>
