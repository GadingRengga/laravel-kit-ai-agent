{{--
    <x-button.dropdown-item> — Satu baris item di dalam dropdown menu.
    Otomatis jadi <a> kalau diberi `href`, atau <button type="button"> kalau tidak.

    Contoh:
        <x-button.dropdown-item icon="fa-solid fa-pencil">Edit</x-button.dropdown-item>
        <x-button.dropdown-item icon="fa-solid fa-trash-can" danger>Hapus</x-button.dropdown-item>
        <x-button.dropdown-item icon="fa-regular fa-bell" :badge="3">Notifikasi</x-button.dropdown-item>
        <x-button.dropdown-item disabled>Laporan (segera)</x-button.dropdown-item>
        <x-button.dropdown-item href="/profile">Profil Saya</x-button.dropdown-item>
        <x-button.dropdown-item wire:click="approve(1)" keep-open>Approve</x-button.dropdown-item>

        {{-- Grup "check list" (mis. pilihan urutan) — klik salah satu otomatis
             menandai yang lain tidak aktif, tanpa JS tambahan --}}
        <x-button.dropdown-section>
            <x-button.dropdown-item :checked="true">Terbaru</x-button.dropdown-item>
            <x-button.dropdown-item :checked="false">Terlama</x-button.dropdown-item>
        </x-button.dropdown-section>

    Props:
        icon          class FontAwesome (mis. "fa-solid fa-pencil")
        href          jika diisi, render sebagai <a>
        danger        bool — style merah untuk aksi destruktif
        disabled      bool
        checked       null|bool — set selain null untuk jadikan item "check-list"
        badge         string|int — badge kecil di kanan item
        badge-variant primary | success | danger | warning | neutral   (default: primary)
        keep-open     bool — jangan tutup dropdown saat item ini diklik
                      (default: dropdown otomatis tertutup tiap item diklik)
--}}
@props([
    'icon' => null,
    'href' => null,
    'danger' => false,
    'disabled' => false,
    'checked' => null,
    'badge' => null,
    'badgeVariant' => 'primary',
    'keepOpen' => false,
])

@php
    $isCheckItem = !is_null($checked);
    $tag = ($href && !$disabled) ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{
        $attributes->class([
            'nt-dd-item',
            'w-full',
            'nt-dd-item-danger' => $danger,
            'nt-dd-item-disabled' => $disabled,
            'nt-dd-item-check' => $isCheckItem,
            'checked' => $isCheckItem && $checked,
        ])
    }}
    @if($tag === 'a')
        href="{{ $href }}"
    @else
        type="button"
    @endif
    @if($disabled) disabled @endif
    @if($keepOpen) data-nt-dropdown-keep-open @endif
>
    @if($isCheckItem)
        <i class="nt-dd-icon {{ $checked ? 'fa-solid fa-check' : '' }}"></i>
    @elseif($icon)
        <i class="nt-dd-icon {{ $icon }}"></i>
    @endif

    {{ $slot }}

    @if(!is_null($badge))
        <span class="nt-dd-badge nt-dd-badge-{{ $badgeVariant }}">{{ $badge }}</span>
    @endif
</{{ $tag }}>
