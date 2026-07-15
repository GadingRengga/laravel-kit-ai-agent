<div id="user-role-panel" live-scope="Superuser.UserRoleController">

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
    </div>

    <x-table searchable id="user-role-table">
        <x-slot:head>
            <th class="nt-min-w-200">User</th>
            <th>Email</th>
            <th>Role Saat Ini</th>
            <th class="nt-th-right">Aksi</th>
        </x-slot:head>

        @forelse ($users as $user)
            <x-table.row>
                <x-table.cell class="font-medium text-slate-700 dark:text-slate-200">{{ $user->name }}</x-table.cell>
                <x-table.cell class="text-[13px] text-slate-400">{{ $user->email }}</x-table.cell>
                <x-table.cell>
                    @forelse ($user->roles as $role)
                        <span class="nt-badge nt-badge-info mr-1">{{ $role->name }}</span>
                    @empty
                        <span class="text-[13px] text-slate-400">—</span>
                    @endforelse
                </x-table.cell>
                <x-table.cell align="right">
                    <x-table.actions>
                        <x-table.action icon="fa-solid fa-shield-halved" title="Atur Role" data-nt-modal-btn
                            data-nt-modal-target="modal-sm" live-click="edit({{ $user->id }})"
                            live-target="#modal-sm" />
                    </x-table.actions>
                </x-table.cell>
            </x-table.row>
        @empty
            <tr>
                <td colspan="4" class="text-center text-[13px] text-slate-400 py-8">
                    Belum ada user terdaftar.
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
