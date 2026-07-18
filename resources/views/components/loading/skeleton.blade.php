{{--
    <x-loading.skeleton> — Placeholder shimmer selagi konten asli dimuat.

    Contoh:
        <x-loading.skeleton variant="text" width="100" />
        <x-loading.skeleton variant="avatar" />
        <x-loading.skeleton variant="thumb" />
        <x-loading.skeleton variant="title" />
        <x-loading.skeleton variant="badge" />
        <x-loading.skeleton variant="btn" />

        {{-- Card gabungan (pakai <x-loading.skeleton-card> untuk komposisi siap pakai) --}}

    Props:
        variant   text | title | avatar | avatar-sm | thumb | badge | btn      (default: text)
        width     int|string|null — lebar dalam persen/skala 0-100 (khusus variant text), mis. 60, 75, 90, 100
--}}
@props([
    'variant' => 'text',
    'width' => null,
])

@php
    $variantClass = match ($variant) {
        'title' => 'nt-skeleton-title',
        'avatar' => 'nt-skeleton-avatar',
        'avatar-sm' => 'nt-skeleton-avatar-sm',
        'thumb' => 'nt-skeleton-thumb',
        'badge' => 'nt-skeleton-badge',
        'btn' => 'nt-skeleton-btn',
        default => 'nt-skeleton-text',
    };

    $widthClass = ($variant === 'text' && $width) ? "w-{$width}" : null;
@endphp

<span {{ $attributes->class(['nt-skeleton', $variantClass, $widthClass]) }}></span>
