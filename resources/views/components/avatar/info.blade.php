{{--
    <x-avatar.info> — Baris avatar + nama + sub-label. Cocok untuk tabel, list, dan card header.

    Contoh:
        <x-avatar.info name="Gading Devanta" sub="Administrator · Jakarta" status="online"
            initials="GD" color="gradient-indigo" />

        <x-avatar.info name="Rani Kusuma" sub="Frontend Engineer · Bandung" status="busy"
            src="https://api.dicebear.com/9.x/avataaars/svg?seed=Rani" />

    Props:
        name      string — nama utama
        sub       string|null — sub-label (role, lokasi, dll)
        size      diteruskan ke <x-avatar> (default: md)
        semua props <x-avatar> lain (src, initials, icon, color, status, ring, shape) turut didukung.
--}}
@props([
    'name',
    'sub' => null,
    'src' => null,
    'initials' => null,
    'icon' => null,
    'size' => 'md',
    'shape' => 'circle',
    'color' => null,
    'status' => null,
    'ring' => null,
])

<div {{ $attributes->class(['nt-avatar-info']) }}>
    <x-avatar :src="$src" :initials="$initials" :icon="$icon" :size="$size" :shape="$shape"
        :color="$color" :status="$status" :ring="$ring" />
    <div>
        <div class="nt-avatar-info-name">{{ $name }}</div>
        @if($sub)
            <div class="nt-avatar-info-sub">{{ $sub }}</div>
        @endif
    </div>
</div>
