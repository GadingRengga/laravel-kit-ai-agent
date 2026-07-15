<div id="role-panel" live-scope="Superuser.RoleController">

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
        <p class="comp-section-title">Daftar Role</p>

        <div class="flex items-center gap-2">
            <x-modal.button target="modal-sm" variant="primary" size="sm" icon="fa-solid fa-plus"
                live-click="create" live-target="#modal-sm" live-loading="#role-form-modal">
                Tambah Role
            </x-modal.button>
        </div>
    </div>

    <x-table searchable id="role-table">
        <x-slot:head>
            <th class="nt-min-w-200">Role</th>
            <th>Slug</th>
            <th class="nt-th-center">User</th>
            <th class="nt-th-center">Permission</th>
            <th class="nt-th-center">Status</th>
            <th class="nt-th-right">Aksi</th>
        </x-slot:head>

        @forelse ($roles as $role)
            <x-table.row>
                <x-table.cell class="font-medium text-slate-700 dark:text-slate-200">
                    {{ $role->name }}
                </x-table.cell>
                <x-table.cell class="font-mono text-[12px] text-slate-400">{{ $role->slug }}</x-table.cell>
                <x-table.cell align="center">{{ $role->users_count }}</x-table.cell>
                <x-table.cell align="center">{{ $role->permissions_count }}</x-table.cell>
                <x-table.cell align="center">
                    @if ($role->is_active)
                        <span class="nt-badge nt-badge-success">Aktif</span>
                    @else
                        <span class="nt-badge nt-badge-danger">Nonaktif</span>
                    @endif
                </x-table.cell>
                <x-table.cell align="right">
                    <x-table.actions>
                        <x-table.action icon="fa-regular fa-pen-to-square" title="Edit" data-nt-modal-btn
                            data-nt-modal-target="modal-sm" live-click="edit({{ $role->id }})"
                            live-target="#modal-sm" live-loading="#role-form-modal" />
                        @if ($role->slug !== 'super_user')
                            <x-table.action icon="fa-regular fa-trash-can" title="Hapus" danger
                                confirm="Yakin hapus role "{{ $role->name }}"?"
                                live-click="destroy({{ $role->id }})" live-target="#role-panel"
                                live-loading="#role-panel" />
                        @endif
                    </x-table.actions>
                </x-table.cell>
            </x-table.row>
        @empty
            <tr>
                <td colspan="6" class="text-center text-[13px] text-slate-400 py-8">
                    Belum ada role. Klik "Tambah Role" untuk membuat role pertama.
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
