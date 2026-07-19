{{-- Main Card --}}
<x-card>
    <x-slot:header>
        <div>
            <p class="comp-section-title">Daftar User</p>
            <p class="comp-section-desc">Kelola seluruh akun user, role, dan status aktif.</p>
        </div>
    </x-slot:header>

    <x-slot:actions>
        <x-modal.button target="modal-sm" variant="primary" size="sm" icon="fa-solid fa-plus" live-click="create"
            live-target="#modal-sm">
            Tambah User
        </x-modal.button>
    </x-slot:actions>

    <x-table searchable id="user-table" compact striped>
        <x-slot:head>
            <th style="min-width: 200px;">User</th>
            <th>Username</th>
            <th>Karyawan</th>
            <th class="nt-th-center">Role</th>
            <th class="nt-th-center">Status</th>
            <th class="nt-th-center">Login</th>
            <th class="nt-th-right">Status</th>
            <th class="nt-th-right">History</th>
            <th class="nt-th-right">Aksi</th>
        </x-slot:head>

        @forelse ($users as $user)
            <x-table.row>
                {{-- User Info --}}
                <x-table.cell>
                    <div class="flex items-center gap-3">
                        {{-- Avatar --}}
                        @if ($user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                class="w-9 h-9 min-w-[36px] rounded-full object-cover ring-2 ring-slate-100 dark:ring-slate-700">
                        @else
                            <div @class([
                                'w-9 h-9 min-w-[36px] rounded-full flex items-center justify-center ring-2 shadow-sm',
                                'bg-gradient-to-br from-amber-400 to-orange-500 ring-amber-200 dark:ring-amber-700' => $user->isSuperUser(),
                                'bg-gradient-to-br from-primary-400 to-primary-600 ring-primary-200 dark:ring-primary-700' => !$user->isSuperUser(),
                            ])>
                                <span class="text-white text-[12px] font-bold">
                                    {{ collect(explode(' ', $user->name))->map(fn($w) => substr($w, 0, 1))->take(2)->implode('') }}
                                </span>
                            </div>
                        @endif

                        {{-- Name & Meta --}}
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-medium text-slate-700 dark:text-slate-200 truncate">
                                    {{ $user->name }}
                                </span>
                                @if ($user->isSuperUser())
                                    <x-icon.tile name="crown" variant="warning" size="xs" circle />
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <i class="fa-regular fa-envelope text-[10px] text-slate-400"></i>
                                <a href="mailto:{{ $user->email }}"
                                    class="text-[12px] text-primary-600 dark:text-primary-400 hover:underline truncate">
                                    {{ $user->email }}
                                </a>
                            </div>
                        </div>
                    </div>
                </x-table.cell>

                {{-- Username --}}
                <x-table.cell>
                    @if ($user->username)
                        <code
                            class="px-2 py-0.5 text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded">
                            {{ $user->username }}
                        </code>
                    @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </x-table.cell>

                {{-- Employee --}}
                <x-table.cell>
                    @if ($user->employee)
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-briefcase text-[10px] text-slate-400"></i>
                            <div>
                                <span class="text-[13px] text-slate-600 dark:text-slate-300">
                                    {{ $user->employee->name }}
                                </span>
                                @if ($user->employee->employee_code)
                                    <span class="text-[11px] text-slate-400 ml-0.5">
                                        #{{ $user->employee->employee_code }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </x-table.cell>

                {{-- Role --}}
                <x-table.cell align="center">
                    <x-table.status-cell>
                        @if ($user->roles_count > 0)
                            <span class="nt-badge nt-badge-info"
                                title="{{ $user->roles->pluck('name')->implode(', ') }}">
                                <i class="fa-solid fa-shield-halved text-[10px] mr-1"></i>
                                {{ $user->roles_count }}
                            </span>
                        @else
                            <span class="nt-badge nt-badge-warning">
                                <i class="fa-solid fa-circle-exclamation text-[10px] mr-1"></i>
                                0
                            </span>
                        @endif
                    </x-table.status-cell>
                </x-table.cell>

                {{-- Status --}}
                <x-table.cell align="center">
                    <x-table.status-cell>
                        @if ($user->is_active)
                            <span class="nt-badge nt-badge-success">
                                <i class="fa-solid fa-circle text-[6px] mr-1.5 text-emerald-400"></i>
                                Aktif
                            </span>
                        @else
                            <span class="nt-badge nt-badge-danger">
                                <i class="fa-solid fa-circle text-[6px] mr-1.5 text-red-400"></i>
                                Nonaktif
                            </span>
                        @endif
                    </x-table.status-cell>
                </x-table.cell>

                {{-- Last Login --}}
                <x-table.cell align="center" class="whitespace-nowrap">
                    @if ($user->last_login_at)
                        <div class="flex flex-col items-center gap-0.5">
                            <span class="text-[12px] text-slate-500 dark:text-slate-400"
                                title="{{ $user->last_login_at->format('d M Y H:i') }}">
                                {{ $user->last_login_at->diffForHumans() }}
                            </span>
                            @if ($user->last_login_ip)
                                <span class="text-[10px] font-mono text-slate-400/60 dark:text-slate-500/60">
                                    <i class="fa-solid fa-network-wired text-[8px] mr-0.5"></i>
                                    {{ $user->last_login_ip }}
                                </span>
                            @endif
                        </div>
                    @else
                        <span class="text-[11px] text-slate-400/50 dark:text-slate-500/50 italic">
                            Belum login
                        </span>
                    @endif
                </x-table.cell>

                {{-- Actions --}}
                <x-table.cell align="right">
                    <x-table.actions>
                        <x-table.action icon="fa-regular fa-pen-to-square" title="Edit User" data-nt-modal-btn
                            data-nt-modal-target="modal-sm" live-click="edit({{ $user->id }})"
                            live-target="#modal-sm" live-loading="#user-form-modal" />

                        @if (!$user->isSuperUser())
                            <x-table.action icon="fa-regular fa-trash-can" title="Hapus User" danger
                                live-click="destroy({{ $user->id }})" live-target="#user-panel"
                                live-loading="#loading" live-callback-before="confirmDelete"
                                live-callback-after="alert" />
                        @endif
                    </x-table.actions>
                </x-table.cell>
            </x-table.row>
        @empty
            <tr>
                <td colspan="7" class="text-center py-12">
                    <div class="flex flex-col items-center gap-3">
                        <div
                            class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <i class="fa-solid fa-users-slash text-2xl text-slate-300 dark:text-slate-600"></i>
                        </div>
                        <div>
                            <p class="text-[14px] font-medium text-slate-500 dark:text-slate-400">
                                Belum ada user
                            </p>
                            <p class="text-[12px] text-slate-400 dark:text-slate-500 mt-0.5">
                                Klik tombol "Tambah User" untuk membuat user pertama.
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-table>

    @if ($users->count() > 0)
        <x-slot:footer>
            <div class="flex items-center justify-between w-full">
                <span class="text-[12px] text-slate-400  p-3">
                    <i class="fa-regular fa-user mr-1"></i>
                    {{ number_format($users->count()) }} total user
                    <span class="mx-1.5">•</span>
                    <i class="fa-regular fa-circle-check text-emerald-400 mr-1"></i>
                    {{ number_format($users->where('is_active', true)->count()) }} aktif
                    <span class="mx-1.5">•</span>
                    <i class="fa-regular fa-circle-xmark text-red-400 mr-1"></i>
                    {{ number_format($users->where('is_active', false)->count()) }} nonaktif
                </span>
            </div>
        </x-slot:footer>
    @endif
</x-card>
