@props([
    'name',
    'label' => null,
    'value',
    'checked' => false,
])

@php
    $id = $attributes->get('id', $name . '_' . Str::slug($value));
    $old = old($name);
    $isChecked = $old !== null ? ((string) $old === (string) $value) : $checked;
@endphp

<label class="form-radio-wrap" for="{{ $id }}">
    <input
        type="radio"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        @checked($isChecked)
        {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => 'form-radio']) }}
    />
    <span class="form-radio-label">{{ $label ?? $slot }}</span>
</label>
