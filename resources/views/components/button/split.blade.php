{{--
    <x-button.split> — Tombol aksi utama + toggle dropdown di sampingnya.
    Butuh netra-dropdown.js (lihat README) — tidak perlu id manual.

    Contoh:
        <x-button.split variant="primary" icon="fa-solid fa-floppy-disk" wire:click="save">
            Simpan

            <x-slot:menu>
                <x-button.dropdown-section>
                    <x-button.dropdown-item icon="fa-solid fa-floppy-disk">Simpan</x-button.dropdown-item>
                    <x-button.dropdown-item icon="fa-solid fa-clone">Duplikat & Simpan</x-button.dropdown-item>
                </x-button.dropdown-section>
            </x-slot:menu>
        </x-button.split>

    Props:
        variant   sama seperti <x-button> (default: primary)
        size      sm | md | lg | xl        (default: md)
        icon      ikon di tombol utama
        align     left | right — posisi dropdown menu   (default: left)
        up        bool — buka menu ke atas               (default: false)

    Semua atribut lain (wire:click, onclick, dll) diteruskan ke TOMBOL UTAMA,
    bukan ke wrapper — supaya klik langsung ke aksi utamanya.
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'align' => 'left',
    'up' => false,
])

<div class="nt-dropdown" data-nt-dropdown>
    <div class="nt-btn-split">
        <x-button :variant="$variant" :size="$size" :icon="$icon" {{ $attributes }}>
            {{ $slot }}
        </x-button>
        <x-button
            :variant="$variant"
            :size="$size"
            data-nt-dropdown-toggle
            aria-haspopup="true"
            aria-expanded="false"
            title="Buka opsi lain"
        >
            <i class="fa-solid fa-chevron-down text-[10px]"></i>
        </x-button>
    </div>

    <div @class([
        'nt-dropdown-menu',
        'align-right' => $align === 'right',
        'drop-up' => $up,
    ]) data-nt-dropdown-menu>
        {{ $menu }}
    </div>
</div>
