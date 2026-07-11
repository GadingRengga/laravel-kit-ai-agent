@props(['label'])

<div class="nt-detail-item">
    <div class="nt-detail-label">{{ $label }}</div>
    <div {{ $attributes->class('nt-detail-value') }}>{{ $slot }}</div>
</div>
