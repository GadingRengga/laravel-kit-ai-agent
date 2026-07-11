@props([
    'icon' => null,
    'title' => null,
    'href' => null,
    'danger' => false,
    'confirm' => null,
])

@php
    $tag = $href ? 'a' : 'button';
    $classes = ['nt-action-btn', $danger ? 'danger' : ''];
@endphp

<{{ $tag }} {{ $attributes->class($classes) }}
    @if ($href) href="{{ $href }}" @endif
    @if ($title) title="{{ $title }}" @endif
    @if ($confirm) data-nt-confirm="{{ $confirm }}" @endif>
    @if ($icon)
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
    </{{ $tag }}>
