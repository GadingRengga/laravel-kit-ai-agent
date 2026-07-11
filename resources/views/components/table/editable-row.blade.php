@props(['rowId'])

<tr {{ $attributes }} data-nt-row data-row-id="{{ $rowId }}">
    {{ $slot }}
</tr>
