@props([
    'id' => null,
])

@php
    $treeId = $id ?? 'nt-tree-' . \Illuminate\Support\Str::random(6);
@endphp

<div class="nt-table-wrap nt-table-wrap-flush">
    <table {{ $attributes->class('nt-table') }} id="{{ $treeId }}" data-nt-tree-table>
        <thead>
            <tr>{{ $head }}</tr>
        </thead>
        <tbody id="{{ $treeId }}-tbody">
            {{ $slot }}
        </tbody>
    </table>
</div>
