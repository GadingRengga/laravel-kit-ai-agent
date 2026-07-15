<div id="permission-panel" live-scope="Superuser.PermissionController">

    @if ($error ?? null)
        <div class="stat-card px-4 py-3 mb-4 text-[13px] text-red-600 dark:text-red-400">
            <i class="fa-solid fa-circle-exclamation mr-1.5"></i>{{ $error }}
        </div>
    @endif

    @if ($success ?? null)
        <div class="stat-card px-4 py-3 mb-4 text-[13px] text-emerald-600 dark:text-emerald-400">
            <i class="fa-solid fa-circle-check mr-1.5"></i>{{ $success }}
        </div>
    @endif

    <div class="comp-section-header flex items-center justify-between mb-3">
        <p class="comp-section-title">Daftar Permission</p>

        <div class="flex items-center gap-2">
            <x-modal.button target="modal-sm" variant="primary" size="sm" icon="fa-solid fa-plus"
                live-click="create" live-target="#modal-sm" live-loading="#permission-form-modal">
                Tambah Permission
            </x-modal.button>
        </div>
    </div>

    <x-table searchable id="permission-table">
        <x-slot:head>
            <th class="nt-min-w-200">Permission</th>
            <th>Slug</th>
            <th>Group</th>
            <th class="nt-th-center">Role Terkait</th>
            <th class="nt-th-center">Menu Terkait</th>
            <th class="nt-th-right">Aksi</th>
        </x-slot:head>

        @forelse ($permissions as $perm)
            <x-table.row>
                <x-table.cell class="font-medium text-slate-700 dark:text-slate-200">{{ $perm->name }}</x-table.cell>
                <x-table.cell class="font-mono text-[12px] text-slate-400">{{ $perm->slug }}</x-table.cell>
                <x-table.cell>
                    @if ($perm->group)
                        <span class="nt-badge nt-badge-info">{{ $perm->group }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </x-table.cell>
                <x-table.cell align="center">{{ $perm->roles_count }}</x-table.cell>
                <x-table.cell align="center">{{ $perm->menus_count }}</x-table.cell>
                <x-table.cell align="right">
                    <x-table.actions>
                        <x-table.action icon="fa-regular fa-pen-to-square" title="Edit" data-nt-modal-btn
                            data-nt-modal-target="modal-sm" live-click="edit({{ $perm->id }})"
                            live-target="#modal-sm" live-loading="#permission-form-modal" />
                        <x-table.action icon="fa-regular fa-trash-can" title="Hapus" danger
                            confirm="Yakin hapus permission "{{ $perm->name }}"?"
                            live-click="destroy({{ $perm->id }})" live-target="#permission-panel"
                            live-loading="#permission-panel" />
                    </x-table.actions>
                </x-table.cell>
            </x-table.row>
        @empty
            <tr>
                <td colspan="6" class="text-center text-[13px] text-slate-400 py-8">
                    Belum ada permission. Klik "Tambah Permission" untuk membuat permission pertama.
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
