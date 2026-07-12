@php
    // Modal ini dibuka lewat target #modal-lg (shell generik di footer.blade.php,
    // sama seperti #modal-sm dipakai fitur Menu). id="position-access-modal"
    // dipasang di root <x-modal.content> supaya bisa jadi target
    // live-loading, sama polanya dengan "menu-form-modal" di fitur Menu.
@endphp

<x-modal.content title="Akses Menu — {{ $position->name }}"
    subtitle="{{ $position->department?->name ? 'Departemen ' . $position->department->name : 'Tanpa departemen' }}"
    live-scope="Superuser.PositionMenuController">

    <input type="hidden" name="position_id" value="{{ $position->id }}">

    <p class="text-[13px] text-slate-400 mb-3">
        Centang menu & hak akses yang boleh dipakai posisi ini. Menu yang "Lihat"-nya tidak dicentang otomatis tidak
        muncul di sidebar untuk posisi ini.
    </p>

    <div class="nt-table-wrap nt-table-wrap-bare" style="max-height: 55vh; overflow-y: auto;">
        <table class="nt-table nt-table-compact">
            <thead>
                <tr>
                    <th class="nt-min-w-200">Menu</th>
                    <th class="nt-th-center">Lihat</th>
                    <th class="nt-th-center">Tambah</th>
                    <th class="nt-th-center">Ubah</th>
                    <th class="nt-th-center">Hapus</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $menu)
                    @include('pages.superuser.position-menu.partials._access-row', [
                        'menu' => $menu,
                        'level' => 1,
                        'position' => $position,
                    ])
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-[13px] text-slate-400 py-6">
                            Belum ada menu terdaftar. Buat menu dulu lewat halaman Manajemen Menu.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>Batal</button>

        <x-button type="button" variant="primary" icon="fa-solid fa-floppy-disk" live-click="saveAccess"
            live-target="#position-menu-panel" live-loading="#position-access-modal" data-nt-modal-close>
            Simpan Akses
        </x-button>
    </x-slot:footer>
</x-modal.content>
