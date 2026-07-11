{{--
    <x-icon.tile> — Kotak ikon berwarna (bulat/rounded), pengganti pola
    ad-hoc "w-12 h-12 rounded-xl bg-indigo-50 ... flex items-center
    justify-center" yang sebelumnya ditulis ulang inline di tiap halaman.

    Contoh:
        <x-icon.tile name="arrow-trend-up" />                          {{-- soft primary, size md --}}
        <x-icon.tile name="users" variant="success" size="lg" />
        <x-icon.tile name="bell" variant="danger" tone="solid" />
        <x-icon.tile name="user" circle size="xl" />                   {{-- avatar-style circle --}}

    Props:
        name      diteruskan ke <x-icon> (nama ikon tanpa prefix "fa-")
        icon      diteruskan ke <x-icon> (override class penuh)
        style     diteruskan ke <x-icon> (solid|regular|light|duotone|brands)
        variant   primary | success | danger | warning | info | neutral   (default: primary)
        tone      soft (bg tint + ikon berwarna) | solid (bg penuh + ikon putih)  (default: soft)
        size      sm | md | lg | xl                                        (default: md)
        circle    bool — border-radius penuh (untuk gaya avatar)
--}}
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
    {{
        $attributes->class([
            'nt-icon-box',
            "nt-icon-box-{$size}",
            "nt-icon-box-{$tone}-{$variant}",
            'nt-icon-box-circle' => $circle,
        ])
    }}
>
    <x-icon :name="$name" :icon="$icon" :style="$style" />
</div>
