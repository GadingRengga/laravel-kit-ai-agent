@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
    'default' => null,       // "17:30"
    'increment' => null,     // menit loncat, contoh 15 atau 30
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

    <div class="nt-timepicker-wrap">
        <input
            type="text"
            name="{{ $name }}"
            id="{{ $id }}"
            @if($value) value="{{ $value }}" @endif
            @if($default) data-default-time="{{ $default }}" @endif
            @if($increment) data-minute-increment="{{ $increment }}" @endif
            {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => 'nt-timepicker' . ($error ? ' is-error' : '')]) }}
        />
    </div>

    @if($error)
        <span class="form-msg-error"><i class="fa-solid fa-circle-exclamation text-[11px]"></i>{{ $error }}</span>
    @elseif($hint)
        <span class="form-hint">{{ $hint }}</span>
    @endif
</div>
