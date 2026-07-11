@props([
    'name',
    'label' => null,
    'hint' => null,
    'default' => '#2d5aff',
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $value = old($name, $default);
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}" class="input-label">{{ $label }}</label>
    @endif

    <div class="nt-colorpicker" data-default-color="{{ $value }}" {{ $attributes->has('disabled') ? 'data-disabled="true"' : '' }}>
        <div class="nt-colorpicker-wrap {{ $attributes->has('disabled') ? 'nt-cp-disabled' : '' }}">
            <div class="nt-cp-swatch">
                <div class="nt-cp-swatch-inner" style="background:{{ $value }}"></div>
            </div>
            <input class="nt-cp-text" type="text" value="{{ $value }}" placeholder="#000000" readonly />
            <i class="nt-cp-icon fa-solid fa-chevron-down"></i>
        </div>
        {{-- Nilai sebenarnya yang ikut ter-submit --}}
        <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" {{ $attributes->except(['id', 'class']) }} />
    </div>

    @if($error)
        <span class="form-msg-error"><i class="fa-solid fa-circle-exclamation text-[11px]"></i>{{ $error }}</span>
    @elseif($hint)
        <span class="form-hint">{{ $hint }}</span>
    @endif
</div>

@once
    <script>
        document.addEventListener('ak:color', function (e) {
            const hidden = e.target.querySelector('input[type="hidden"]');
            if (hidden) hidden.value = e.detail.hex;
        });
    </script>
@endonce
