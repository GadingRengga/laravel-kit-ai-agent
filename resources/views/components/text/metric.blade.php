{{--
    <x-text.metric> — Teks skor/metrik berwarna (mis. kolom performa di
    tabel: rating, growth %, dll). Class: .nt-score-{variant}[-bold]

    Contoh:
        <x-text.metric variant="success">98.2</x-text.metric>
        <x-text.metric variant="primary" bold>4.9</x-text.metric>
        <x-text.metric variant="warning">72%</x-text.metric>

    Props:
        variant   primary | success | warning   (default: primary)
        bold      bool — pakai varian -bold (lebih tebal, tanpa font-size override)
--}}
@props([
    'variant' => 'primary',
    'bold' => false,
])

<span {{
    $attributes->class([
        $bold ? "nt-score-{$variant}-bold" : "nt-score-{$variant}",
    ])
}}>{{ $slot }}</span>
