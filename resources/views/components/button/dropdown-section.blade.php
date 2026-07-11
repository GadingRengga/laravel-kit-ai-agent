{{--
    <x-button.dropdown-section> — Bungkus sekelompok <x-button.dropdown-item>.
    Pemisah antar grup pakai <x-button.dropdown-divider />.
--}}
<div {{ $attributes->class(['nt-dd-section']) }}>
    {{ $slot }}
</div>
