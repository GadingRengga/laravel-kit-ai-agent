{{--
    <x-avatar> — Avatar tunggal Netra UI (inisial, gambar, atau ikon fallback).

    Contoh:
        <x-avatar initials="GD" color="gradient-indigo" size="lg" />
        <x-avatar src="https://api.dicebear.com/9.x/avataaars/svg?seed=Netra" size="xl" />
        <x-avatar icon="fa-regular fa-user" size="xl" />
        <x-avatar initials="GD" color="gradient-indigo" size="lg" status="online" />
        <x-avatar initials="VR" color="gradient-violet" size="xl" shape="square" ring="indigo" />
        <x-avatar loading size="md" />

    Props:
        src       string|null — URL gambar avatar
        initials  string|null — inisial teks kalau tidak ada gambar (mis. "GD")
        icon      string|null — class FontAwesome untuk fallback ikon (dipakai kalau src & initials kosong)
        size      xs | sm | md | lg | xl | 2xl | 3xl                       (default: md)
        shape     circle | square | rounded                                (default: circle)
        color     nama warna tint/solid/gradient, mis. "indigo", "solid-slate", "gradient-ocean" (opsional)
        status    online | busy | away | offline | null                    (default: null, tanpa badge)
        ring      indigo | success | white | null — highlight border       (default: null)
        pulse     bool — animasi denyut untuk indikator "live"
        loading   bool — tampilkan skeleton shimmer, mengabaikan konten lain

    Kalau status diisi, komponen otomatis dibungkus <span class="nt-avatar-wrap"> + badge status.
--}}
@props([
    'src' => null,
    'initials' => null,
    'icon' => null,
    'size' => 'md',
    'shape' => 'circle',
    'color' => null,
    'status' => null,
    'ring' => null,
    'pulse' => false,
    'loading' => false,
])

@php
    $shapeClass = match ($shape) {
        'square' => 'nt-avatar-square',
        'rounded' => 'nt-avatar-rounded',
        default => null,
    };

    $ringClass = $ring ? "nt-avatar-ring-{$ring}" : null;
@endphp

@php
    $avatarInner = function () use ($src, $initials, $icon) {
        if ($src) {
            return '<img src="' . e($src) . '" alt="' . e($initials ?? 'Avatar') . '" />';
        }
        if ($initials) {
            return e($initials);
        }
        if ($icon) {
            return '<i class="' . e($icon) . '"></i>';
        }
        return '';
    };
@endphp

@if($status)
    <span class="nt-avatar-wrap">
        <div {{ $attributes->class([
            'nt-avatar',
            "nt-avatar-{$size}",
            $shapeClass,
            $color ? "nt-avatar-{$color}" : null,
            'nt-avatar-icon' => $icon && !$src && !$initials,
            'nt-avatar-pulse' => $pulse,
            'nt-avatar-skeleton' => $loading,
        ]) }}>{!! $loading ? '' : $avatarInner() !!}</div>
        <span class="nt-avatar-status nt-status-{{ $status }}"></span>
    </span>
@else
    <div {{ $attributes->class([
        'nt-avatar',
        "nt-avatar-{$size}",
        $shapeClass,
        $color ? "nt-avatar-{$color}" : null,
        'nt-avatar-icon' => $icon && !$src && !$initials,
        'nt-avatar-pulse' => $pulse,
        'nt-avatar-skeleton' => $loading,
    ]) }}>{!! $loading ? '' : $avatarInner() !!}</div>
@endif
