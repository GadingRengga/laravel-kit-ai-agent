@props([
    'target', // sama dengan target di <x-table.collapse-row>
])

<td {{ $attributes->class('nt-tree-indent nt-tree-indent-2') }}>
    <button class="nt-expand-btn" data-nt-expand-btn data-target="{{ $target }}">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
</td>
