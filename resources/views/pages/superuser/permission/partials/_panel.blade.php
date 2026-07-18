<div id="permission-panel" live-scope="Superuser.PermissionController">

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
                <p class="comp-section-title">Daftar Permission</p>
                <p class="comp-section-desc">Atur permission yang digunakan oleh role dan menu di seluruh aplikasi.</p>
            </div>
        </x-slot:header>

        <x-slot:actions>
            <x-modal.button target="modal-sm" variant="primary" size="sm" icon="fa-solid fa-plus" live-click="create"
                live-target="#modal-sm" live-loading="#permission-form-modal">
                Tambah Permission
            </x-modal.button>
        </x-slot:actions>

        <x-table searchable id="permission-table" compact striped>
            <x-slot:head>
                <th style="min-width: 200px;">Permission</th>
                <th>Slug</th>
                <th>Group</th>
                <th class="nt-th-center">Role</th>
                <th class="nt-th-center">Menu</th>
                <th class="nt-th-right">Aksi</th>
            </x-slot:head>

            @forelse ($permissions as $perm)
                <x-table.row>
                    <x-table.cell class="font-medium text-slate-700 dark:text-slate-200">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-8 h-8 min-w-[32px] rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center ring-2 ring-emerald-200 dark:ring-emerald-700 shadow-sm">
                                <i class="fa-solid fa-key text-white text-[12px]"></i>
                            </div>
                            <span>{{ $perm->name }}</span>
                        </div>
                    </x-table.cell>
                    <x-table.cell>
                        <code
                            class="px-2 py-0.5 text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded">
                            {{ $perm->slug }}
                        </code>
                    </x-table.cell>
                    <x-table.cell>
                        @if ($perm->group)
                            <span class="nt-badge nt-badge-info">
                                <i class="fa-solid fa-folder text-[10px] mr-1"></i>{{ $perm->group }}
                            </span>
                        @else
                            <span class="text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell align="center">
                        <span class="nt-badge nt-badge-primary">
                            <i class="fa-solid fa-shield text-[10px] mr-1"></i>{{ $perm->roles_count }}
                        </span>
                    </x-table.cell>
                    <x-table.cell align="center">
                        <span class="nt-badge nt-badge-warning">
                            <i class="fa-solid fa-sitemap text-[10px] mr-1"></i>{{ $perm->menus_count }}
                        </span>
                    </x-table.cell>
                    <x-table.cell align="right">
                        <x-table.actions>
                            <x-table.action icon="fa-regular fa-pen-to-square" title="Edit Permission" data-nt-modal-btn
                                data-nt-modal-target="modal-sm" live-click="edit({{ $perm->id }})"
                                live-target="#modal-sm" live-loading="#permission-form-modal" />
                            <x-table.action icon="fa-regular fa-trash-can" title="Hapus Permission" danger
                                confirm="Yakin ingin menghapus permission {{ $perm->name }}?"
                                live-click="destroy({{ $perm->id }})" live-target="#permission-panel"
                                live-loading="#permission-panel" />
                        </x-table.actions>
                    </x-table.cell>
                </x-table.row>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-12">
                        <div class="flex flex-col items-center gap-3">
                            <div
                                class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <i class="fa-solid fa-lock text-2xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                            <div>
                                <p class="text-[14px] font-medium text-slate-500 dark:text-slate-400">
                                    Belum ada permission
                                </p>
                                <p class="text-[12px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    Klik tombol "Tambah Permission" untuk membuat permission pertama.
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-table>

        @if ($permissions->count() > 0)
            <x-slot:footer>
                <div class="flex items-center justify-between w-full">
                    <span class="text-[12px] text-slate-400">
                        <i class="fa-regular fa-key mr-1"></i>
                        {{ number_format($permissions->count()) }} total permission
                        <span class="mx-1.5">&bull;</span>
                        <i class="fa-solid fa-folder text-indigo-400 mr-1"></i>
                        {{ number_format($permissions->pluck('group')->unique()->filter()->count()) }} group
                    </span>
                </div>
            </x-slot:footer>
        @endif
    </x-card>
</div>
