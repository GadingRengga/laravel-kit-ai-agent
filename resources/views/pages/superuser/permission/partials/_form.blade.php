@php
    $isEdit = $permission->exists;
@endphp

<x-modal.content title="{{ $isEdit ? 'Edit Permission' : 'Tambah Permission' }}"
    subtitle="{{ $isEdit ? $permission->name : 'Buat permission baru dan tautkan ke menu' }}"
    live-scope="Superuser.PermissionController">

    <div class="space-y-3">
        <x-forms.input type="hidden" name="id" label="" value="{{ $permission->id }}" />

        <x-forms.input name="name" label="Nama Permission" required value="{{ $permission->name }}"
            placeholder="mis. Lihat Data Karyawan" />

        <x-forms.input name="slug" label="Slug" value="{{ $permission->slug }}"
            placeholder="Kosongkan untuk otomatis dari nama" hint="Huruf kecil, angka, titik. Contoh: employee.view" />

        <x-forms.input name="group" label="Group" value="{{ $permission->group }}"
            placeholder="mis. employee, user_management" hint="Gunakan untuk mengelompokkan permission" />

        <x-forms.textarea name="description" label="Deskripsi" value="{{ $permission->description }}"
            placeholder="Penjelasan singkat tentang permission ini" rows="2" />

        <div>
            <label class="text-[12px] font-medium text-slate-600 dark:text-slate-300 block mb-2">Tautan ke Menu</label>
            <p class="text-[11px] text-slate-400 mb-2">Centang menu yang ingin muncul di sidebar saat user memiliki
                permission ini.</p>
            <div class="nt-table-wrap nt-table-wrap-bare" style="max-height: 250px; overflow-y: auto;">
                <table class="nt-table nt-table-compact">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="nt-th-center">Terhubung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($menus as $menu)
                            @include('pages.superuser.permission.partials._menu-row', [
                                'menu' => $menu,
                                'level' => 1,
                                'selectedMenuIds' => $selectedMenuIds,
                            ])
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-[13px] text-slate-400 py-4">
                                    Belum ada menu. Buat menu dulu lewat Manajemen Menu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>Batal</button>

        <x-button type="button" variant="primary" icon="fa-solid fa-floppy-disk"
            live-click="{{ $isEdit ? 'update' : 'store' }}" live-target="#permission-panel"
            live-loading="#permission-form-modal" data-nt-modal-close>
            Simpan
        </x-button>
    </x-slot:footer>
</x-modal.content>
