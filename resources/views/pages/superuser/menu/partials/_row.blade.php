@php
    $hasChildren = $menu->children->isNotEmpty();
@endphp

<x-table.tree-row node-id="menu-{{ $menu->id }}" parent="{{ $level > 1 ? 'menu-' . $menu->parent_id : '' }}"
    level="{{ $level }}" icon="{{ $menu->icon ?: 'fa-solid fa-circle-dot' }}" label="{{ $menu->name }}"
    :expandable="$hasChildren" :children-target-id="false">

    @if ($hasChildren)
        <x-slot:toggle>
            {{-- :loaded="true" karena children sudah eager-loaded langsung sebagai baris di bawah,
                 bukan lazy-load lewat AJAX --}}
            <x-table.tree-toggle node-id="menu-{{ $menu->id }}" :loaded="true" />
        </x-slot:toggle>
    @endif

    <x-table.cell class="font-mono text-[12px] text-slate-400">{{ $menu->route ?: '—' }}</x-table.cell>
    <x-table.cell align="center">{{ $menu->order }}</x-table.cell>
    <x-table.cell align="center">
        @if ($menu->is_active)
            <span class="nt-badge nt-badge-success">Aktif</span>
        @else
            <span class="nt-badge nt-badge-danger">Nonaktif</span>
        @endif
    </x-table.cell>
    <x-table.cell align="right">
        <x-table.actions>
            <x-table.action icon="fa-regular fa-pen-to-square" title="Edit" data-nt-modal-btn
                data-nt-modal-target="modal-sm" live-click="edit({{ $menu->id }})" live-target="#modal-sm"
                live-loading="#menu-form-modal" />
            <x-table.action icon="fa-regular fa-trash-can" title="Hapus" danger
                confirm="Yakin hapus menu &quot;{{ $menu->name }}&quot;?" live-click="destroy({{ $menu->id }})"
                live-target="#menu-panel" live-loading="#menu-panel" />
        </x-table.actions>
    </x-table.cell>
</x-table.tree-row>

@if ($hasChildren)
    @foreach ($menu->children as $child)
        @include('pages.superuser.menu.partials._row', ['menu' => $child, 'level' => $level + 1])
    @endforeach
@endif
