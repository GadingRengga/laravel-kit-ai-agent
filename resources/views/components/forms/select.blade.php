@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
    'options' => null,        // array ['value' => 'Label'] atau list biasa, atau null utk pakai <option> manual di slot
    'placeholder' => null,    // "Pilih kategori…"
    'searchable' => false,    // true = pakai TomSelect (class nt-select)
    'multiple' => false,
    'maxItems' => null,       // hanya untuk multiple + searchable
    'success' => false,
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $state = $error ? 'is-error' : ($success ? 'is-success' : '');
    $selected = old($name, $attributes->get('value'));
    $fieldName = $multiple ? "{$name}[]" : $name;
    $selectedArr = $multiple ? (array) ($selected ?? []) : [$selected];
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }} @if($required)<span class="required">*</span>@endif
        </label>
    @endif

    <select
        name="{{ $fieldName }}"
        id="{{ $id }}"
        @if($multiple) multiple @endif
        @if($searchable)
            @if($placeholder) data-placeholder="{{ $placeholder }}" @endif
            @if($maxItems) data-max-items="{{ $maxItems }}" @endif
        @endif
        {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => $searchable ? 'nt-select' : "form-select {$state}"]) }}
    >
        @if(!$searchable && $placeholder)
            <option value="" disabled {{ empty(array_filter($selectedArr)) ? 'selected' : '' }}>{{ $placeholder }}</option>
        @endif

        @if($options)
            @foreach($options as $value => $optLabel)
                @php $value = is_int($value) ? $optLabel : $value; @endphp
                <option value="{{ $value }}" @selected(in_array((string) $value, array_map('strval', $selectedArr)))>
                    {{ $optLabel }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    @if($error)
        <span class="form-msg-error"><i class="fa-solid fa-circle-exclamation text-[11px]"></i>{{ $error }}</span>
    @elseif(is_string($success) && $success)
        <span class="form-msg-success"><i class="fa-solid fa-circle-check text-[11px]"></i>{{ $success }}</span>
    @elseif($hint)
        <span class="form-hint">{{ $hint }}</span>
    @endif
</div>
