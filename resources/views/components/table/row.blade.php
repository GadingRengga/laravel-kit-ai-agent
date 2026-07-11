{{--
    <x-table.row> — <tr> pembungkus baris data.

    Prop:
      clickable — cuma nambah cursor-pointer, tanpa behavior. Pakai ini kalau
                  kamu mau pasang handler klik sendiri (wire:click, live-click,
                  onclick manual, dsb) lewat $attributes.
      href      — kalau diisi, SELURUH baris jadi klikable ke URL ini via
                  satu delegated listener terpusat (lihat js/netra-tables-patch.js),
                  tanpa perlu tulis onclick manual tiap baris.

    Klik pada elemen di dalam baris yang match ".col-check", ".nt-cell-actions",
    "[live-click]", atau "[data-nt-confirm]" TIDAK akan memicu navigasi row,
    supaya checkbox/tombol aksi tetap berfungsi normal.
--}}
@props([
    'clickable' => false,
    'href' => null,
])

<tr {{ $attributes->class([$clickable || $href ? 'cursor-pointer' : '']) }}
    @if ($href) data-row-href="{{ $href }}" @endif>
    {{ $slot }}
</tr>
