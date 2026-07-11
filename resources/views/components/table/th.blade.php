@props([
    'col'     => null,          // key kolom, wajib diisi jika sortable — dibaca JS via data-nt-sort-col
    'sortable'=> false,
    'default' => null,          // 'asc' | 'desc' — kondisi sort awal (opsional)
    'align'   => 'left',        // left | right | center
])

@php
    $alignClass = match ($align) {
        'right'  => 'nt-text-right',
        'center' => 'nt-text-center',
        default  => '',
    };
@endphp

@if ($sortable)
    <th
        {{ $attributes->class([
            'nt-th-sort',
            $alignClass,
            $default => $default,
        ]) }}
        data-nt-sort-col="{{ $col }}"
    >
        {{ $slot }}
        <span class="nt-sort-icon"><i class="fa-solid fa-sort-up"></i><i class="fa-solid fa-sort-down"></i></span>
    </th>
@else
    <th {{ $attributes->class([$alignClass]) }}>{{ $slot }}</th>
@endif
