{{--
    <x-table.check-cell> — Sel checkbox per baris.

    Tidak ada onclick inline di sini. stopPropagation ditangani sekali oleh
    delegated listener di js/netra-tables-patch.js, supaya klik checkbox tidak
    ikut memicu navigasi row (kalau <x-table.row href="..."> dipakai) dan
    tidak menambah markup berulang di setiap baris.
--}}
<td {{ $attributes->class('col-check') }}>
    <input type="checkbox" class="form-checkbox row-check" />
</td>
