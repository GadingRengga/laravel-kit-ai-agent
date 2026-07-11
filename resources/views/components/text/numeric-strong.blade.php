{{--
    <x-text.numeric-strong> — Sama seperti <x-text.numeric> tapi lebih
    tebal & lebih gelap, untuk angka total/grand total. Class: .nt-num-strong

    Contoh:
        <x-text.numeric-strong>Rp 12.400.000</x-text.numeric-strong>
--}}
@props(['tag' => 'span'])

<{{ $tag }} {{ $attributes->class(['nt-num-strong']) }}>{{ $slot }}</{{ $tag }}>
