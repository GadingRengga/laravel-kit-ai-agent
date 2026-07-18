@props([
    'name' => null,
    'icon' => null,
    'style' => 'solid',
    'variant' => 'primary',
    'tone' => 'soft',
    'size' => 'md',
    'circle' => false,
])

<div
    {{ $attributes->class([
        'nt-icon-box',
        "nt-icon-box-{$size}",
        "nt-icon-box-{$tone}-{$variant}",
        'nt-icon-box-circle' => $circle,
    ]) }}>
    <x-icon :name="$name" :icon="$icon" :style="$style" />
</div>
