@props(['name', 'label', 'value' => 1, 'checked' => false, 'hint' => null])

@php
    $id = $attributes->get('id', $name);
    $describedBy = trim(($hint ? "$id-help" : '').' '.($errors->has($name) ? "$id-error" : ''));
@endphp

<div>
    <div class="flex items-start gap-3">
        <input
            id="{{ $id }}"
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked(old($name, $checked))
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @error($name) aria-invalid="true" @enderror
            {{ $attributes->except('id')->merge(['class' => 'mt-1 shrink-0']) }}
        >
        <div>
            <label for="{{ $id }}" class="text-sm font-semibold leading-5 text-slate-700">{{ $label }}</label>
            @if ($hint)
                <p id="{{ $id }}-help" class="ui-help">{{ $hint }}</p>
            @endif
        </div>
    </div>
    @error($name)
        <p id="{{ $id }}-error" class="ui-error">{{ $message }}</p>
    @enderror
</div>
