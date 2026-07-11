@props([
    'target', // id dari <x-table.detail-row> pasangannya, TANPA tanda '#'
])

<tr {{ $attributes->class('cursor-pointer') }} data-nt-collapse-row data-target="{{ $target }}">
    {{ $slot }}
</tr>
