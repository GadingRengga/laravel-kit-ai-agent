{{--
    <x-card.placeholder> — Kartu placeholder untuk konten yang belum
    tersedia (mis. chart/activity feed tahap berikutnya), seperti pola
    di admin-dashboard.html.

    Contoh:
        <x-card.placeholder
            icon="chart-bar"
            style="regular"
            title="Chart content — Tahap 2"
            subtitle="Akan diisi pada tahap berikutnya"
            class="lg:col-span-2 min-h-72"
        />

    Props:
        icon      nama ikon (tanpa prefix "fa-")
        style     diteruskan ke <x-icon> (default: regular)
        title     judul utama
        subtitle  teks kecil di bawah judul (opsional)
--}}
@props([
    'icon' => null,
    'style' => 'regular',
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->class(['stat-card', 'p-6', 'flex', 'flex-col', 'items-center', 'justify-center', 'gap-3']) }}>
    <x-icon.tile :name="$icon" :style="$style" variant="primary" size="lg" />
    @if($title)
        <p class="nt-placeholder-title">{{ $title }}</p>
    @endif
    @if($subtitle)
        <p class="nt-placeholder-subtitle">{{ $subtitle }}</p>
    @endif
</div>
