@php
    $isEdit = $role->exists;
@endphp

<x-modal.content title="{{ $isEdit ? 'Edit Role' : 'Tambah Role' }}"
    subtitle="{{ $isEdit ? $role->name : 'Buat role baru dengan permission yang sesuai' }}"
    live-scope="Superuser.RoleController">

    <div class="space-y-3">
        <x-forms.input type="hidden" name="id" label="" value="{{ $role->id }}" />

        <x-forms.input name="name" label="Nama Role" required value="{{ $role->name }}" placeholder="mis. Manager" />

        <x-forms.input name="slug" label="Slug" value="{{ $role->slug }}"
            placeholder="Kosongkan untuk otomatis dari nama" hint="Huruf kecil, angka, strip." />

        <x-forms.textarea name="description" label="Deskripsi" value="{{ $role->description }}"
            placeholder="Penjelasan singkat tentang role ini" rows="2" />

        <x-forms.toggle name="is_active" label="Role Aktif" :checked="(bool) ($role->is_active ?? true)" />

        <div>
            <label class="text-[12px] font-medium text-slate-600 dark:text-slate-300 block mb-2">Permission</label>
            <div class="nt-table-wrap nt-table-wrap-bare" style="max-height: 200px; overflow-y: auto;">
                <table class="nt-table nt-table-compact">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            <th class="nt-th-center">Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $perm)
                            <tr>
                                <td class="text-[13px]">
                                    @if ($perm->group)
                                        <span class="text-[11px] text-slate-400 mr-1">[{{ $perm->group }}]</span>
                                    @endif
                                    {{ $perm->name }}
                                </td>
                                <td class="nt-text-center">
                                    <x-forms.checkbox name="permission_ids[]" value="{{ $perm->id }}"
                                        :checked="in_array($perm->id, $selectedPermissionIds)" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-[13px] text-slate-400 py-4">
                                    Belum ada permission. Buat permission dulu.
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
            live-click="{{ $isEdit ? 'update' : 'store' }}" live-target="#role-panel" live-loading="#role-form-modal"
            data-nt-modal-close>
            Simpan
        </x-button>
    </x-slot:footer>
</x-modal.content>
