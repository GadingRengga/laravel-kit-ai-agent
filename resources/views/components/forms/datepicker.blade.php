@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
    'format' => 'd/m/Y',     // token: d D l dd j F M Y
    'default' => null,       // "2026-06-09" atau "today"
    'min' => null,           // "2000-01-01" atau "today"
    'max' => null,           // "today"
    'mode' => 'single',      // single | range
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $value = old($name, $attributes->get('value'));
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}" class="form-label input-label">
            {{ $label }} @if($required)<span class="required text-red-400">*</span>@endif
        </label>
    @endif

    <input
        type="text"
        name="{{ $name }}"
        id="{{ $id }}"
        @if($value) value="{{ $value }}" @endif
        data-date-format="{{ $format }}"
        @if($default) data-default-date="{{ $default }}" @endif
        @if($min) data-min-date="{{ $min }}" @endif
        @if($max) data-max-date="{{ $max }}" @endif
        @if($mode === 'range') data-mode="range" @endif
        {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => 'nt-datepicker' . ($error ? ' is-error' : '')]) }}
    />

    @if($error)
        <span class="form-msg-error"><i class="fa-solid fa-circle-exclamation text-[11px]"></i>{{ $error }}</span>
    @elseif($hint)
        <span class="form-hint">{{ $hint }}</span>
    @endif
</div>
