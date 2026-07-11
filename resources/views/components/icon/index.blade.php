{{--
    <x-icon> — Wrapper ikon FontAwesome. Menghindari harus mengetik
    `class="fa-solid fa-..."` manual berulang-ulang, plus skala size &
    color yang konsisten dengan warna varian button/chip/text.

    Contoh:
        <x-icon name="trash-can" />                          {{-- fa-solid fa-trash-can --}}
        <x-icon name="trash-can" color="danger" />
        <x-icon name="github" style="brands" />               {{-- fa-brands fa-github --}}
        <x-icon name="heart" style="regular" size="lg" />
        <x-icon name="spinner" spin />
        <x-icon icon="fa-solid fa-arrow-trend-up" size="xl" /> {{-- override manual kalau perlu --}}

    Props:
        name         nama ikon TANPA prefix "fa-" (mis. "trash-can", "user", "check")
        icon         override penuh, isi class FontAwesome lengkap (mis. "fa-brands fa-github")
                     — pakai ini kalau `name` tidak cukup fleksibel
        style        solid | regular | light | duotone | brands   (default: solid)
        size         2xs | xs | sm | md | lg | xl | 2xl            (default: md, ~14px)
        color        primary | success | danger | warning | info | muted | faint | white
                     (default: mengikuti warna teks di sekitarnya)
        spin         bool — animasi berputar (fa-spin), cocok untuk ikon loading
        fixed-width  bool — lebar ikon seragam (fa-fw), berguna untuk list icon+label sejajar

    Semua atribut lain (title, aria-hidden, dll) diteruskan ke <i>.
--}}
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

<i
    {{
        $attributes->class([
            'nt-icon',
            $iconClass,
            "nt-icon-{$size}",
            "nt-icon-{$color}" => $color,
            'fa-spin' => $spin,
            'fa-fw' => $fixedWidth,
        ])
    }}
    @if(!$attributes->has('aria-hidden')) aria-hidden="true" @endif
></i>
