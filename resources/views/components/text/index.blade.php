{{--
    <x-text> — Wrapper teks dengan skala size/color/weight yang konsisten
    dengan sistem warna Netra UI, supaya tidak perlu hafal kombinasi
    class custom (nt-text-*) + Tailwind berulang-ulang.

    Contoh:
        <x-text>Teks biasa</x-text>
        <x-text size="2xl" weight="bold">$84.2k</x-text>
        <x-text color="muted" size="sm">Deskripsi singkat di bawah judul</x-text>
        <x-text color="danger" size="xs">Wajib diisi</x-text>
        <x-text tag="label" for="email" weight="medium">Email</x-text>
        <x-text truncate>Teks yang sangat panjang dan akan dipotong dengan ellipsis…</x-text>
        <x-text mono size="sm">INV-2024-00123</x-text>

    Props:
        tag       elemen HTML yang dipakai (p, span, div, label, h1..h6, dst)  (default: p)
        size      2xs | xs | sm | base | lg | xl | 2xl                        (default: base)
        weight    normal | medium | semibold | bold                           (default: normal)
        color     strong | muted | faint | soft | accent | primary
                  | success | danger | warning | info | white
                  (default: mewarisi warna teks di sekitarnya)
        mono      bool — pakai font DM Mono (untuk kode/angka/SKU)
        truncate  bool — potong dengan ellipsis kalau kepanjangan (1 baris)

    Semua atribut lain diteruskan ke elemen root.
--}}
@props([
    'tag' => 'p',
    'size' => 'base',
    'weight' => 'normal',
    'color' => null,
    'mono' => false,
    'truncate' => false,
])

@php
    $sizeClass = match ($size) {
        '2xs' => 'nt-text-2xs',
        'xs' => 'nt-text-xs',
        'sm' => 'nt-text-sm',
        'lg' => 'nt-text-lg',
        'xl' => 'nt-text-xl',
        '2xl' => 'nt-text-2xl',
        default => null, // base — mewarisi ukuran default di sekitarnya
    };

    $colorClass = match ($color) {
        'strong' => 'nt-text-strong',
        'muted' => 'nt-text-muted2',
        'faint' => 'nt-text-faint',
        'soft' => 'nt-text-soft',
        'accent' => 'nt-text-accent',
        'primary' => 'nt-text-primary',
        'success' => 'nt-text-success',
        'danger' => 'nt-text-danger',
        'warning' => 'nt-text-warning',
        'info' => 'nt-text-info',
        'white' => 'nt-text-white',
        default => null,
    };

    $weightClass = match ($weight) {
        'medium' => 'font-medium',
        'semibold' => 'font-semibold',
        'bold' => 'font-bold',
        default => 'font-normal',
    };
@endphp

<{{ $tag }}
    {{
        $attributes->class([
            $sizeClass,
            $colorClass,
            $weightClass,
            'nt-font-mono' => $mono,
            'truncate' => $truncate,
        ])
    }}
>{{ $slot }}</{{ $tag }}>
