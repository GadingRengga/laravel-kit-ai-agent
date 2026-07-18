{{--
    <x-loading.overlay> — Overlay loading full-page atau khusus dalam sebuah card.

    Cara pakai paling umum adalah JS API (overlay di-generate otomatis, tidak perlu markup ini sama sekali):
        ntLoading.show('Menyimpan perubahan…');   // full-page
        ntLoading.hide();
        ntLoading.showIn('nt-card-overlay-demo', 'Memuat data…');   // di dalam elemen ber-id tertentu
        ntLoading.hideIn('nt-card-overlay-demo');

    Tapi kalau kamu ingin merender overlay secara statis dari Blade (mis. saat SSR loading state awal),
    pakai komponen ini di dalam parent ber-`position: relative`:

        <div class="relative" style="min-height:160px">
            ...konten card...
            <x-loading.overlay text="Memuat data…" contained />
        </div>

    Props:
        text        string|null — teks di bawah spinner (opsional)
        contained   bool — true = overlay relatif terhadap parent (card), false = full-page fixed
        open        bool — true = langsung tampil (class is-open), default true untuk render statis
        spinner     ring | dual | arc | dots | bars | ripple     (default: dual)
--}}
@props([
    'text' => null,
    'contained' => true,
    'open' => true,
    'spinner' => 'dual',
])

<div {{ $attributes->class([
    'nt-loading-overlay',
    'is-contained' => $contained,
    'is-open' => $open,
]) }}>
    <div class="nt-loading-card">
        <x-loading.spinner :style="$spinner" size="lg" />
        @if($text)
            <p class="nt-loading-text">{{ $text }}</p>
        @endif
    </div>
</div>
