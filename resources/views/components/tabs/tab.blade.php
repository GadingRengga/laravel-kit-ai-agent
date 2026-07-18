{{--
    <x-tabs.tab> — Satu item tab, dipakai di dalam <x-slot:tabs> milik <x-tabs>.

    Contoh:
        <x-tabs.tab value="overview" active>Overview</x-tabs.tab>
        <x-tabs.tab value="activity" badge="12">Activity</x-tabs.tab>
        <x-tabs.tab value="billing" badge="New">Billing</x-tabs.tab>
        <x-tabs.tab value="profile" icon="fa-regular fa-user">Profile</x-tabs.tab>
        <x-tabs.tab href="/dashboard" value="dashboard">Dashboard</x-tabs.tab>  {{-- untuk variant="folder" / mode="href" --}}

    Props:
        value    string — id tab, harus cocok dengan `value` di <x-tabs.panel> pasangannya
        active   bool — tandai sebagai tab aktif awal
        icon     string|null — class FontAwesome di depan label
        badge    string|int|null — counter/label kecil di kanan teks
        href     string|null — kalau diisi, tab dirender sebagai <a> (dipakai untuk variant="folder")
--}}
@props([
    'value',
    'active' => false,
    'icon' => null,
    'badge' => null,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{ $attributes->class(['nt-tab']) }}
    @if($tag === 'a') href="{{ $href }}" @else type="button" @endif
    data-tab="{{ $value }}"
    @if($active) aria-selected="true" @endif
>
    @if($icon)
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
    @if(!is_null($badge))
        <span class="nt-tab-badge">{{ $badge }}</span>
    @endif
</{{ $tag }}>
