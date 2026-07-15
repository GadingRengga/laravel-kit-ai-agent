<div id="user-panel" live-scope="Superuser.UserController">

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
        <p class="comp-section-title">Daftar User</p>

        <div class="flex items-center gap-2">
            <x-modal.button target="modal-sm" variant="primary" size="sm" icon="fa-solid fa-plus"
                live-click="create" live-target="#modal-sm" live-loading="#user-form-modal">
                Tambah User
            </x-modal.button>
        </div>
    </div>

    <x-table searchable id="user-table">
        <x-slot:head>
            <th class="nt-min-w-200">Nama</th>
            <th>Username</th>
            <th>Email</th>
            <th class="nt-th-center">Role</th>
            <th class="nt-th-center">Status</th>
            <th class="nt-th-right">Aksi</th>
        </x-slot:head>

        @forelse ($users as $user)
            <x-table.row>
                <x-table.cell class="font-medium text-slate-700 dark:text-slate-200">
                    <div class="flex items-center gap-2">
                        @if ($user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div
                                class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                                <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                            </div>
                        @endif
                        <span>{{ $user->name }}</span>
                    </div>
                </x-table.cell>
                <x-table.cell class="font-mono text-[12px] text-slate-400">{{ $user->username }}</x-table.cell>
                <x-table.cell class="text-[13px]">{{ $user->email }}</x-table.cell>
                <x-table.cell align="center">
                    @if ($user->roles_count > 0)
                        <span class="nt-badge nt-badge-info">{{ $user->roles_count }} Role</span>
                    @else
                        <span class="nt-badge nt-badge-warning">Belum ada role</span>
                    @endif
                </x-table.cell>
                <x-table.cell align="center">
                    @if ($user->is_active)
                        <span class="nt-badge nt-badge-success">Aktif</span>
                    @else
                        <span class="nt-badge nt-badge-danger">Nonaktif</span>
                    @endif
                </x-table.cell>
                <x-table.cell align="right">
                    <x-table.actions>
                        <x-table.action icon="fa-regular fa-pen-to-square" title="Edit" data-nt-modal-btn
                            data-nt-modal-target="modal-sm" live-click="edit({{ $user->id }})"
                            live-target="#modal-sm" live-loading="#user-form-modal" />
                        <x-table.action icon="fa-solid fa-user-tag" title="Role" data-nt-modal-btn
                            data-nt-modal-target="modal-sm" live-click="editRole({{ $user->id }})"
                            live-target="#modal-sm" live-loading="#user-form-modal" />
                        @if (!$user->isSuperUser())
                            <x-table.action icon="fa-solid fa-toggle-{{ $user->is_active ? 'on' : 'off' }}"
                                title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                live-click="toggleStatus({{ $user->id }})" live-target="#user-panel"
                                live-loading="#user-panel" />
                            <x-table.action icon="fa-regular fa-trash-can" title="Hapus" danger
                                confirm="Yakin hapus user {{ $user->name }}?"
                                live-click="destroy({{ $user->id }})" live-target="#user-panel"
                                live-loading="#user-panel" />
                        @endif
                    </x-table.actions>
                </x-table.cell>
            </x-table.row>
        @empty
            <tr>
                <td colspan="6" class="text-center text-[13px] text-slate-400 py-8">
                    Belum ada user. Klik "Tambah User" untuk membuat user pertama.
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
