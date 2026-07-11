{{--
    <x-button> — Komponen inti Netra UI Button.

    Contoh:
        <x-button>Simpan</x-button>
        <x-button variant="danger" icon="fa-solid fa-trash">Hapus</x-button>
        <x-button variant="outline-success" size="sm">Approve</x-button>
        <x-button variant="soft-primary" icon="fa-regular fa-bell" :badge="4">Notifikasi</x-button>
        <x-button icon-only icon="fa-solid fa-pencil" title="Edit" variant="ghost" />
        <x-button href="/export" icon-trailing="fa-solid fa-download">Export</x-button>
        <x-button loading loading-text="Menyimpan…">Simpan</x-button>
        <x-button wire:click="save" wire:loading.attr="disabled">Simpan</x-button>

    Props:
        variant       primary | secondary | success | danger | warning | info
                      | outline | outline-success | outline-danger | outline-warning | outline-info
                      | soft-primary | soft-success | soft-danger | soft-warning | soft-info
                      | ghost | ghost-primary                       (default: primary)
        size          sm | md | lg | xl                             (default: md)
        icon          class FontAwesome untuk ikon di depan teks (mis. "fa-solid fa-check")
        icon-trailing class FontAwesome untuk ikon di belakang teks
        icon-only     bool — tombol persegi berisi ikon saja (wajib sertakan `title` untuk aksesibilitas)
        pill          bool — border-radius penuh (999px)
        loading       bool — tampilkan spinner, otomatis nonaktif & non-klik
        loading-text  string — teks pengganti slot saat loading (opsional)
        badge         string|int — counter badge di dalam tombol
        href          jika diisi, komponen render sebagai <a> bukan <button>
        type          button | submit | reset                        (default: button, diabaikan jika `href` diisi)
        disabled      bool

    Semua atribut lain (onclick, wire:click, x-on:click, data-*, dll)
    otomatis diteruskan ke elemen root.
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconTrailing' => null,
    'iconOnly' => false,
    'pill' => false,
    'loading' => false,
    'loadingText' => null,
    'badge' => null,
    'href' => null,
    'type' => 'button',
    'disabled' => false,
])

@php
    $variantClass = match ($variant) {
        'primary' => 'nt-btn-primary',
        'secondary' => 'nt-btn-secondary',
        'success' => 'nt-btn-success',
        'danger' => 'nt-btn-danger',
        'warning' => 'nt-btn-warning',
        'info' => 'nt-btn-info',
        'outline' => 'nt-btn-outline',
        'outline-success' => 'nt-btn-outline-success',
        'outline-danger' => 'nt-btn-outline-danger',
        'outline-warning' => 'nt-btn-outline-warning',
        'outline-info' => 'nt-btn-outline-info',
        'soft-primary' => 'nt-btn-soft-primary',
        'soft-success' => 'nt-btn-soft-success',
        'soft-danger' => 'nt-btn-soft-danger',
        'soft-warning' => 'nt-btn-soft-warning',
        'soft-info' => 'nt-btn-soft-info',
        'ghost' => 'nt-btn-ghost',
        'ghost-primary' => 'nt-btn-ghost nt-btn-ghost-primary',
        default => 'nt-btn-primary',
    };

    $sizeClass = match ($size) {
        'sm' => 'nt-btn-sm',
        'lg' => 'nt-btn-lg',
        'xl' => 'nt-btn-xl',
        default => null,
    };

    $isDisabled = $disabled || $loading;
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{
        $attributes->class([
            'nt-btn',
            $variantClass,
            $sizeClass,
            'nt-btn-icon' => $iconOnly,
            'nt-btn-pill' => $pill,
            'nt-btn-loading' => $loading,
        ])
    }}
    @if($tag === 'a')
        href="{{ $isDisabled ? '#' : $href }}"
        @if($isDisabled) aria-disabled="true" tabindex="-1" @endif
    @else
        type="{{ $type }}"
        @if($isDisabled) disabled @endif
    @endif
    @if($iconOnly && !$attributes->has('aria-label') && $attributes->has('title'))
        aria-label="{{ $attributes->get('title') }}"
    @endif
>
    @if($loading)
        <span class="nt-btn-spinner"></span>
    @elseif($icon)
        <i class="{{ $icon }}"></i>
    @endif

    @if($loading && $loadingText)
        {{ $loadingText }}
    @else
        {{ $slot }}
    @endif

    @if($iconTrailing && !$loading)
        <i class="{{ $iconTrailing }}"></i>
    @endif

    @if(!is_null($badge))
        <span class="nt-btn-badge">{{ $badge }}</span>
    @endif
</{{ $tag }}>
