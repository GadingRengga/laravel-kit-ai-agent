{{--
    <x-tabs.panel> — Satu panel konten, dipakai di dalam slot default milik <x-tabs>.

    Contoh:
        <x-tabs.panel value="overview">
            <p>Ringkasan performa project minggu ini.</p>
        </x-tabs.panel>
        <x-tabs.panel value="activity" hidden>
            <p>12 aktivitas baru dari tim kamu.</p>
        </x-tabs.panel>

    Props:
        value    string — id panel, harus cocok dengan `value` di <x-tabs.tab> pasangannya
        hidden   bool — sembunyikan panel di render awal (dipakai untuk semua panel non-aktif
                 pertama; panel pertama/aktif biasanya tanpa `hidden`)
--}}
@props([
    'value',
    'hidden' => false,
])

<div {{ $attributes->class(['nt-tab-panel']) }} data-panel="{{ $value }}" @if($hidden) hidden @endif>
    {{ $slot }}
</div>
