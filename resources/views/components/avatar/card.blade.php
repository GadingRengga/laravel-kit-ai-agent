{{--
    <x-avatar.card> — Kartu profil dengan banner, avatar, dan statistik. Cocok untuk halaman tim/directory.

    Contoh:
        <x-avatar.card name="Gading Devanta" role="Administrator" banner="indigo" status="online"
            initials="GD" color="gradient-indigo"
            :stats="['Posts' => 128, 'Followers' => '4.2k', 'Following' => 312]" />

        <x-avatar.card name="Rani Kusuma" role="Frontend Engineer" banner="sky" status="busy"
            src="https://api.dicebear.com/9.x/avataaars/svg?seed=Rani"
            :stats="['Posts' => 84, 'Followers' => '1.8k', 'Following' => 210]" />

    Props:
        name      string
        role      string|null
        banner    default | sky | emerald | rose      (default: default)
        status    online | busy | away | offline | null
        stats     array ['Label' => value] — ditampilkan sebagai 3 kolom statistik (opsional)
        semua props <x-avatar> (src, initials, icon, color, ring) turut didukung, size dikunci "xl".
--}}
@props([
    'name',
    'role' => null,
    'banner' => 'default',
    'status' => null,
    'src' => null,
    'initials' => null,
    'icon' => null,
    'color' => null,
    'ring' => 'white',
    'stats' => null,
])

@php
    $bannerClass = $banner !== 'default' ? "nt-avatar-card-banner-{$banner}" : null;
@endphp

<div {{ $attributes->class(['nt-avatar-card']) }}>
    <div class="nt-avatar-card-banner {{ $bannerClass }}"></div>
    <div class="nt-avatar-card-body">
        <div class="nt-avatar-card-avatar-wrap">
            <x-avatar :src="$src" :initials="$initials" :icon="$icon" size="xl" :color="$color"
                :ring="$ring" :status="$status" />
        </div>
        <p class="nt-avatar-card-name">{{ $name }}</p>
        @if($role)
            <p class="nt-avatar-card-role">{{ $role }}</p>
        @endif

        @if($stats)
            <div class="nt-avatar-card-stats">
                @foreach($stats as $label => $value)
                    <div class="nt-avatar-card-stat">
                        <div class="nt-avatar-card-stat-value">{{ $value }}</div>
                        <div class="nt-avatar-card-stat-label">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
