{{--
    <x-card.statistic> — Kartu KPI/statistik seperti di admin-dashboard.html
    ("Revenue", "Users", "Orders", dst). Ini SEKALIGUS PERBAIKAN BUG:
    class `.stat-card` / `.stat-icon-wrap` / `.stat-icon` dipakai di
    admin-dashboard.html tapi tidak pernah didefinisikan di CSS manapun
    di tema asli — lihat netra-card.css yang menyertai paket ini.

    Contoh:
        <x-card.statistic
            label="Revenue"
            value="$84.2k"
            icon="arrow-trend-up"
            trend="12.5%"
            trend-direction="up"
            trend-label="from last month"
        />

        <x-card.statistic
            label="Orders"
            value="1,453"
            icon="bag-shopping"
            variant="warning"
            trend="3.2%"
            trend-direction="down"
            trend-label="from last month"
        />

        {{-- Tanpa trend --}}
        <x-card.statistic label="Active Sessions" value="128" icon="signal" variant="info" />

    Dipakai dalam grid seperti aslinya:
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-card.statistic label="Revenue" value="$84.2k" icon="arrow-trend-up" trend="12.5%" trend-direction="up" trend-label="from last month" />
            <x-card.statistic label="Users" value="24,812" icon="users" trend="8.1%" trend-direction="up" trend-label="from last month" />
            <x-card.statistic label="Orders" value="1,453" icon="bag-shopping" trend="3.2%" trend-direction="down" trend-label="from last month" />
            <x-card.statistic label="Conversion" value="3.68%" icon="bullseye" trend="0.4%" trend-direction="up" trend-label="from last month" />
        </div>

    Props:
        label            label kecil di atas (mis. "Revenue")
        value            nilai utama, sudah diformat (mis. "$84.2k")
        icon             nama ikon untuk <x-icon.tile> (tanpa prefix "fa-")
        variant          primary | success | danger | warning | info   (default: primary)
        trend            teks persentase/selisih (mis. "12.5%"), opsional
        trend-direction  up | down | none                              (default: none)
        trend-label      teks tambahan di belakang trend (mis. "from last month")
--}}
@props([
    'label' => null,
    'value' => null,
    'icon' => null,
    'variant' => 'primary',
    'trend' => null,
    'trendDirection' => 'none',
    'trendLabel' => null,
])

<div {{ $attributes->class(['stat-card', 'p-4']) }}>
    <div class="flex items-center justify-between mb-3">
        <span class="nt-stat-label">{{ $label }}</span>
        <x-icon.tile :name="$icon" :variant="$variant" size="sm" />
    </div>

    <p class="nt-stat-value">{{ $value }}</p>

    @if($trend)
        <p @class(["nt-stat-trend", "nt-stat-trend-{$trendDirection}"])>
            @if($trendDirection === 'up')
                <x-icon name="arrow-up" size="2xs" />
            @elseif($trendDirection === 'down')
                <x-icon name="arrow-down" size="2xs" />
            @endif
            {{ $trend }} {{ $trendLabel }}
        </p>
    @endif
</div>
