{{--
    <x-button.chip> — Label kecil untuk status, filter aktif, atau kategori.

    Contoh:
        <x-button.chip variant="success">Aktif</x-button.chip>
        <x-button.chip variant="danger" dot>Ditolak</x-button.chip>
        <x-button.chip variant="primary" icon="fa-solid fa-tag">Primary</x-button.chip>
        <x-button.chip variant="neutral" removable wire:key="chip-1">MySQL</x-button.chip>

    Props:
        variant     primary | success | danger | warning | info | neutral   (default: neutral)
        dot         bool — titik status kecil di depan label (mis. Online/Away/Offline)
        icon        class FontAwesome di depan label (tidak dipakai bareng `dot`)
        removable   bool — tampilkan tombol × yang menghapus chip dari DOM
                    (butuh netra-chip.js untuk animasi hapusnya)
--}}
@props([
    'variant' => 'neutral',
    'dot' => false,
    'icon' => null,
    'removable' => false,
])

<span {{
    $attributes->class([
        'nt-chip',
        "nt-chip-{$variant}",
        'nt-chip-removable' => $removable,
    ])
}}>
    @if($dot)
        <span class="nt-chip-dot"></span>
    @elseif($icon)
        <i class="{{ $icon }} text-[10px]"></i>
    @endif

    {{ $slot }}

    @if($removable)
        <span class="nt-chip-remove" data-nt-chip-remove role="button" aria-label="Hapus">
            <i class="fa-solid fa-xmark"></i>
        </span>
    @endif
</span>
