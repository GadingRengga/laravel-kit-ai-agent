@props([
    'name' => null,
    'icon' => null,
    'style' => 'solid',
    'size' => 'md',
    'color' => null,
    'spin' => false,
    'fixedWidth' => false,
])

@php
    $prefix = match ($style) {
        'regular' => 'fa-regular',
        'light' => 'fa-light',
        'duotone' => 'fa-duotone',
        'brands' => 'fa-brands',
        default => 'fa-solid',
    };

    $iconClass = $icon ?? ($name ? "{$prefix} fa-{$name}" : null);
@endphp

<i {{ $attributes->class([
    'nt-icon',
    $iconClass,
    "nt-icon-{$size}",
    "nt-icon-{$color}" => $color,
    'fa-spin' => $spin,
    'fa-fw' => $fixedWidth,
]) }}
    @if (!$attributes->has('aria-hidden')) aria-hidden="true" @endif></i>
