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

    @if ($trend)
        <p @class(['nt-stat-trend', "nt-stat-trend-{$trendDirection}"])>
            @if ($trendDirection === 'up')
                <x-icon name="arrow-up" size="2xs" />
            @elseif($trendDirection === 'down')
                <x-icon name="arrow-down" size="2xs" />
            @endif
            {{ $trend }} {{ $trendLabel }}
        </p>
    @endif
</div>
