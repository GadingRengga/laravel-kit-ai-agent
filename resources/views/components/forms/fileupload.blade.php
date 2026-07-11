@props([
    'name',
    'label' => null,
    'hint' => null,
    'accept' => 'image/*',
    'maxSize' => 5,        // MB per file
    'maxFiles' => 1,
    'multiple' => false,
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $fieldName = $multiple || $maxFiles > 1 ? "{$name}[]" : $name;
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}" class="input-label">{{ $label }}</label>
    @endif

    <div class="nt-fileupload" data-max-size="{{ $maxSize }}" data-max-files="{{ $maxFiles }}">
        <div class="nt-fu-zone">
            <input
                type="file"
                name="{{ $fieldName }}"
                id="{{ $id }}"
                accept="{{ $accept }}"
                @if($multiple || $maxFiles > 1) multiple @endif
                {{ $attributes->except(['id', 'class', 'accept'])->merge(['class' => '']) }}
            />
            <i class="nt-fu-icon fa-solid fa-cloud-arrow-up"></i>
            <p class="nt-fu-label"><span>Klik untuk upload</span> atau seret file ke sini</p>
            <p class="nt-fu-hint">{{ $accept }} &middot; Maks. {{ $maxSize }} MB{{ $maxFiles > 1 ? " · Hingga {$maxFiles} file" : '' }}</p>
        </div>
        <div class="nt-fu-list"></div>
    </div>

    @if($error)
        <span class="form-msg-error"><i class="fa-solid fa-circle-exclamation text-[11px]"></i>{{ $error }}</span>
    @elseif($hint)
        <span class="form-hint">{{ $hint }}</span>
    @endif
</div>
