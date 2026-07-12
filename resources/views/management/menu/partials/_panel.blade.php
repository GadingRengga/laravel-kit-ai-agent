<div id="menu-panel" live-scope="Superuser.MenuController">

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
        <p class="comp-section-title">Daftar Menu</p>

        <div class="flex items-center gap-2">
            <x-table.tree-actions target="#menu-tree" />

            <x-modal.button target="modal-sm" variant="primary" size="sm" icon="fa-solid fa-plus"
                live-click="create" live-target="#modal-sm" live-loading="#menu-form-modal">
                Tambah Menu
            </x-modal.button>
        </div>
    </div>

    <x-table.tree id="menu-tree">
        <x-slot:head>
            <th class="nt-min-w-260">Menu</th>
            <th>Route</th>
            <th class="nt-th-center">Urutan</th>
            <th class="nt-th-center">Status</th>
            <th class="nt-th-right">Aksi</th>
        </x-slot:head>

        @forelse ($menus as $menu)
            @include('management.menu.partials._row', ['menu' => $menu, 'level' => 1])
        @empty
            <tr>
                <td colspan="5" class="text-center text-[13px] text-slate-400 py-8">
                    Belum ada menu. Klik "Tambah Menu" untuk membuat menu pertama.
                </td>
            </tr>
        @endforelse
    </x-table.tree>
</div>
