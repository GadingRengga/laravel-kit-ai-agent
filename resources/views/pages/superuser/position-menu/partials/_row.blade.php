<x-table.row>
    <x-table.cell class="font-medium text-slate-700 dark:text-slate-200">{{ $position->name }}</x-table.cell>
    <x-table.cell>{{ $position->department?->name ?? '—' }}</x-table.cell>
    <x-table.cell align="center">{{ $position->level ?? '—' }}</x-table.cell>
    <x-table.cell align="center">
        <span class="nt-badge nt-badge-info">{{ $position->menus_count }} menu</span>
    </x-table.cell>
    <x-table.cell align="right">
        <x-table.actions>
            <x-table.action icon="fa-solid fa-shield-halved" title="Atur Akses Menu" data-nt-modal-btn
                data-nt-modal-target="modal-lg" live-click="editAccess({{ $position->id }})" live-target="#modal-lg" />
        </x-table.actions>
    </x-table.cell>
</x-table.row>
