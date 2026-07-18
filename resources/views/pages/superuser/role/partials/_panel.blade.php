<div id="role-panel" live-scope="Superuser.RoleController">

    {{-- Flash Messages --}}
    @if ($error ?? null)
        <div class="stat-card px-4 py-3 mb-4 text-[13px] text-red-600 dark:text-red-400 flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $error }}</span>
        </div>
    @endif

    @if ($success ?? null)
        <div class="stat-card px-4 py-3 mb-4 text-[13px] text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ $success }}</span>
        </div>
    @endif

    {{-- Main Card --}}
    <x-card>
        <x-slot:header>
            <div>
                <p class="comp-section-title">Daftar Role</p>
                <p class="comp-section-desc">Kelola role dan permission yang dimiliki setiap role.</p>
            </div>
        </x-slot:header>

        <x-slot:actions>
            <x-modal.button target="modal-sm" variant="primary" size="sm" icon="fa-solid fa-plus" live-click="create"
                live-target="#modal-sm" live-loading="#role-form-modal">
                Tambah Role
            </x-modal.button>
        </x-slot:actions>

        <x-table searchable id="role-table" compact striped>
            <x-slot:head>
                <th style="min-width: 200px;">Role</th>
                <th>Slug</th>
                <th class="nt-th-center">User</th>
                <th class="nt-th-center">Permission</th>
                <th class="nt-th-center">Status</th>
                <th class="nt-th-right">Aksi</th>
            </x-slot:head>

            @forelse ($roles as $role)
                <x-table.row>
                    <x-table.cell class="font-medium text-slate-700 dark:text-slate-200">
                        <div class="flex items-center gap-2.5">
                            <div @class([
                                'w-8 h-8 min-w-[32px] rounded-lg flex items-center justify-center ring-2 shadow-sm',
                                'bg-gradient-to-br from-amber-400 to-orange-500 ring-amber-200 dark:ring-amber-700' => $role->isSuperUser(),
                                'bg-gradient-to-br from-indigo-400 to-indigo-600 ring-indigo-200 dark:ring-indigo-700' => !$role->isSuperUser(),
                            ])>
                                @if ($role->isSuperUser())
                                    <i class="fa-solid fa-crown text-white text-[12px]"></i>
                                @else
                                    <i class="fa-solid fa-shield-halved text-white text-[12px]"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <span class="block truncate">{{ $role->name }}</span>
                                @if ($role->isSuperUser())
                                    <span
                                        class="text-[10px] text-amber-500 dark:text-amber-400 font-medium tracking-wide">
                                        <i class="fa-solid fa-crown text-[9px] mr-0.5"></i>SUPER USER
                                    </span>
                                @endif
                            </div>
                        </div>
                    </x-table.cell>
                    <x-table.cell>
                        <code
                            class="px-2 py-0.5 text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded">
                            {{ $role->slug }}
                        </code>
                    </x-table.cell>
                    <x-table.cell align="center">
                        <span class="nt-badge nt-badge-info">
                            <i class="fa-solid fa-users text-[10px] mr-1"></i>{{ $role->users_count }}
                        </span>
                    </x-table.cell>
                    <x-table.cell align="center">
                        <span class="nt-badge nt-badge-primary">
                            <i class="fa-solid fa-shield text-[10px] mr-1"></i>{{ $role->permissions_count }}
                        </span>
                    </x-table.cell>
                    <x-table.cell align="center">
                        <x-table.status-cell>
                            @if ($role->is_active)
                                <span class="nt-badge nt-badge-success">
                                    <i class="fa-solid fa-circle text-[6px] mr-1.5 text-emerald-400"></i>Aktif
                                </span>
                            @else
                                <span class="nt-badge nt-badge-danger">
                                    <i class="fa-solid fa-circle text-[6px] mr-1.5 text-red-400"></i>Nonaktif
                                </span>
                            @endif
                        </x-table.status-cell>
                    </x-table.cell>
                    <x-table.cell align="right">
                        <x-table.actions>
                            <x-table.action icon="fa-regular fa-pen-to-square" title="Edit Role" data-nt-modal-btn
                                data-nt-modal-target="modal-sm" live-click="edit({{ $role->id }})"
                                live-target="#modal-sm" live-loading="#role-form-modal" />
                            @if ($role->slug !== 'super_user')
                                <x-table.action icon="fa-regular fa-trash-can" title="Hapus Role" danger
                                    confirm="Yakin ingin menghapus role {{ $role->name }}?"
                                    live-click="destroy({{ $role->id }})" live-target="#role-panel"
                                    live-loading="#role-panel" />
                            @endif
                        </x-table.actions>
                    </x-table.cell>
                </x-table.row>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-12">
                        <div class="flex flex-col items-center gap-3">
                            <div
                                class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <i class="fa-solid fa-shield-slash text-2xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                            <div>
                                <p class="text-[14px] font-medium text-slate-500 dark:text-slate-400">
                                    Belum ada role
                                </p>
                                <p class="text-[12px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    Klik tombol "Tambah Role" untuk membuat role pertama.
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-table>

        @if ($roles->count() > 0)
            <x-slot:footer>
                <div class="flex items-center justify-between w-full">
                    <span class="text-[12px] text-slate-400">
                        <i class="fa-regular fa-shield mr-1"></i>
                        {{ number_format($roles->count()) }} total role
                        <span class="mx-1.5">&bull;</span>
                        <i class="fa-regular fa-circle-check text-emerald-400 mr-1"></i>
                        {{ number_format($roles->where('is_active', true)->count()) }} aktif
                        <span class="mx-1.5">&bull;</span>
                        <i class="fa-regular fa-circle-xmark text-red-400 mr-1"></i>
                        {{ number_format($roles->where('is_active', false)->count()) }} nonaktif
                    </span>
                </div>
            </x-slot:footer>
        @endif
    </x-card>
</div>
