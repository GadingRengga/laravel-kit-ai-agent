{{--
    <x-avatar.group> — Avatar bertumpuk (stacked) untuk menampilkan anggota tim.

    Contoh:
        <x-avatar.group :more="8">
            <x-avatar initials="GD" color="gradient-indigo" size="md" title="Gading Dev" />
            <x-avatar src="https://api.dicebear.com/9.x/avataaars/svg?seed=Rani" size="md" title="Rani Kusuma" />
            <x-avatar initials="BN" color="gradient-sky" size="md" title="Budi Nugraha" />
        </x-avatar.group>

        <x-avatar.group density="tight" size="lg" :more="3">
            ...
        </x-avatar.group>

    Props:
        density   default | tight | loose      (default: default)
        size      dipakai untuk badge "+N" biar konsisten dengan avatar di dalamnya (default: md)
        more      int|null — jumlah sisa anggota yang ditampilkan sebagai badge "+N"

    Catatan: setiap <x-avatar> di dalam slot sebaiknya dibungkus title/tooltip lewat atribut
    `data-tip` + class `nt-avatar-tip` kalau ingin tooltip nama muncul saat hover (JS: netra-avatar.js).
--}}
@props([
    'density' => 'default',
    'size' => 'md',
    'more' => null,
])

@php
    $densityClass = match ($density) {
        'tight' => 'nt-avatar-group-tight',
        'loose' => 'nt-avatar-group-loose',
        default => null,
    };
@endphp

<div {{ $attributes->class(['nt-avatar-group', $densityClass]) }} role="group">
    {{ $slot }}

    @if(!is_null($more))
        <div class="nt-avatar nt-avatar-{{ $size }} nt-avatar-count">+{{ $more }}</div>
    @endif
</div>
