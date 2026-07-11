@props([
    'id',        // harus sama dengan target di <x-table.collapse-row>
    'colspan',   // = jumlah total kolom pada thead
])

<tr class="nt-detail-row" id="{{ $id }}">
    <td colspan="{{ $colspan }}">
        <div class="nt-detail-inner">
            {{ $slot }}
        </div>
    </td>
</tr>
