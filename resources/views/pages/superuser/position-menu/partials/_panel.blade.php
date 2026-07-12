<div id="position-menu-panel" live-scope="Superuser.PositionMenuController">

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
        <p class="comp-section-title">Daftar Posisi</p>
    </div>

    <x-table searchable id="position-menu-table">
        <x-slot:head>
            <th class="nt-min-w-200">Posisi</th>
            <th>Departemen</th>
            <th class="nt-th-center">Level</th>
            <th class="nt-th-center">Menu Diberi Akses</th>
            <th class="nt-th-right">Aksi</th>
        </x-slot:head>

        @forelse ($positions as $position)
            @include('pages.superuser.position-menu.partials._row', ['position' => $position])
        @empty
            <tr>
                <td colspan="5" class="text-center text-[13px] text-slate-400 py-8">
                    Belum ada posisi. Tambahkan posisi lewat modul Kepegawaian terlebih dahulu.
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
