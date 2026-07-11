@props([
    'name',
    'label' => null,        // label trailing di samping switch
    'value' => '1',
    'checked' => false,
    'size' => 'default',    // sm | default | lg
    'color' => 'brand',     // brand | success | warning | danger
])

@php
    $id = $attributes->get('id', $name);
    $old = old($name);
    $isChecked = $old !== null ? (bool) $old : $checked;

    $sizeClass = match ($size) {
        'sm' => 'toggle-sm',
        'lg' => 'toggle-lg',
        default => '',
    };
    $colorClass = $color !== 'brand' ? "toggle-{$color}" : '';
@endphp

<label class="toggle-wrap {{ $sizeClass }} {{ $colorClass }}" for="{{ $id }}">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        @checked($isChecked)
        {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => 'toggle-input']) }}
    />
    <div class="toggle-track">
        <div class="toggle-thumb"></div>
    </div>
    @if($label)
        <span class="toggle-label">{{ $label }}</span>
    @endif
</label>
