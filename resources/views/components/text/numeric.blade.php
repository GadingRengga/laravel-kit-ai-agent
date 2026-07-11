{{--
    <x-text.numeric> — Teks angka rata kanan, font DM Mono. Untuk sel angka
    di tabel (harga, kuantitas, dsb). Class: .nt-num

    Contoh:
        <x-text.numeric>Rp 1.250.000</x-text.numeric>
        <x-text.numeric tag="td" class="px-4 py-3">42</x-text.numeric>
--}}
@props(['tag' => 'span'])

<{{ $tag }} {{ $attributes->class(['nt-num']) }}>{{ $slot }}</{{ $tag }}>
