{{--
    <x-loading.progress> — Progress bar linear, determinate atau indeterminate.

    Contoh:
        <x-loading.progress :value="35" tone="primary" />
        <x-loading.progress :value="92" tone="success" size="lg" label />
        <x-loading.progress indeterminate />

    Props:
        value           int|null — persentase 0-100 (diabaikan kalau indeterminate)
        tone            primary | accent | success | error | warning        (default: primary)
        size            sm | md | lg                                        (default: md)
        indeterminate   bool — animasi loading tanpa nilai pasti
        label           bool — tampilkan label persen di kanan bar
--}}
@props([
    'value' => 0,
    'tone' => 'primary',
    'size' => 'md',
    'indeterminate' => false,
    'label' => false,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'nt-progress-sm',
        'lg' => 'nt-progress-lg',
        default => null,
    };

    $gradients = [
        'primary' => 'linear-gradient(90deg,var(--nt-alert-primary-solid),#818CF8)',
        'accent' => 'linear-gradient(90deg,var(--nt-alert-accent-solid),#22D3EE)',
        'success' => 'linear-gradient(90deg,var(--nt-alert-success-solid),#4ADE80)',
        'error' => 'linear-gradient(90deg,var(--nt-alert-error-solid),#F87171)',
        'warning' => 'linear-gradient(90deg,var(--nt-alert-warning-solid),#FBBF24)',
    ];
    $gradient = $gradients[$tone] ?? $gradients['primary'];
@endphp

<div class="nt-progress-row">
    <div {{ $attributes->class(['nt-progress', $sizeClass, 'nt-progress-indeterminate' => $indeterminate]) }}
        role="progressbar" @unless($indeterminate) aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100" @endunless>
        <div class="nt-progress-bar" @unless($indeterminate) style="width:{{ $value }}%; background:{{ $gradient }}" @endunless></div>
    </div>
    @if($label && !$indeterminate)
        <span class="nt-progress-label">{{ $value }}%</span>
    @endif
</div>
