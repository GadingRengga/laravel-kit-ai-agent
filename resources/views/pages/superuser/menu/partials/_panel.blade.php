<div id="menu-panel" live-scope="Superuser.MenuController">

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
                <p class="comp-section-title">Daftar Menu</p>
                <p class="comp-section-desc">Atur struktur menu sidebar, urutan, dan status aktif.</p>
            </div>
        </x-slot:header>

        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-table.tree-actions target="#menu-tree" />
                <x-modal.button target="modal-sm" variant="primary" size="sm" icon="fa-solid fa-plus"
                    live-click="create" live-target="#modal-sm" live-loading="#menu-form-modal">
                    Tambah Menu
                </x-modal.button>
            </div>
        </x-slot:actions>

        <x-table.tree id="menu-tree" compact>
            <x-slot:head>
                <th style="min-width: 260px;">Menu</th>
                <th>Route</th>
                <th class="nt-th-center">Urutan</th>
                <th class="nt-th-center">Status</th>
                <th class="nt-th-right">Aksi</th>
            </x-slot:head>

            @forelse ($menus as $menu)
                @include('pages.superuser.menu.partials._row', ['menu' => $menu, 'level' => 1])
            @empty
                <tr>
                    <td colspan="5" class="text-center py-12">
                        <div class="flex flex-col items-center gap-3">
                            <div
                                class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <i class="fa-solid fa-bars-slash text-2xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                            <div>
                                <p class="text-[14px] font-medium text-slate-500 dark:text-slate-400">
                                    Belum ada menu
                                </p>
                                <p class="text-[12px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    Klik tombol "Tambah Menu" untuk membuat menu pertama.
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </x-table>

            @if ($menus->count() > 0)
                <x-slot:footer>
                    <div class="flex items-center justify-between w-full">
                        <span class="text-[12px] text-slate-400">
                            <i class="fa-regular fa-bars mr-1"></i>
                            {{ number_format($menus->count()) }} menu utama
                            <span class="mx-1.5">&bull;</span>
                            <i class="fa-regular fa-circle-check text-emerald-400 mr-1"></i>
                            {{ number_format($menus->where('is_active', true)->count()) }} aktif
                            <span class="mx-1.5">&bull;</span>
                            <i class="fa-regular fa-circle-xmark text-red-400 mr-1"></i>
                            {{ number_format($menus->where('is_active', false)->count()) }} nonaktif
                        </span>
                    </div>
                </x-slot:footer>
            @endif
    </x-card>
</div>
