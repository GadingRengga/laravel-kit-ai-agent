@php
    $isEdit = $user->exists;
@endphp

<x-modal.content title="{{ $isEdit ? 'Edit User' : 'Tambah User' }}"
    subtitle="{{ $isEdit ? $user->name : 'Buat akun user baru' }}" live-scope="Superuser.UserController">

    <div class="space-y-3">
        <x-forms.input type="hidden" name="id" label="" value="{{ $user->id }}" />

        <x-forms.input name="name" label="Nama Lengkap" required value="{{ $user->name }}"
            placeholder="mis. John Doe" />

        <x-forms.input name="username" label="Username" value="{{ $user->username }}"
            placeholder="Kosongkan untuk otomatis dari nama" hint="Huruf kecil, angka, underscore." />

        <x-forms.input type="email" name="email" label="Email" required value="{{ $user->email }}"
            placeholder="user@example.com" />

        <x-forms.input type="password" name="password"
            label="{{ $isEdit ? 'Password (Kosongkan jika tidak diubah)' : 'Password' }}"
            {{ $isEdit ? '' : 'required' }} placeholder="Minimal 8 karakter" />

        <x-forms.input type="password" name="password_confirmation" label="Konfirmasi Password"
            {{ $isEdit ? '' : 'required' }} placeholder="Ulangi password" />

        <x-forms.input name="avatar" label="URL Avatar" value="{{ $user->avatar }}"
            placeholder="https://example.com/avatar.jpg" hint="Kosongkan untuk menggunakan avatar default" />

        <x-forms.toggle name="is_active" label="User Aktif" :checked="(bool) ($user->is_active ?? true)" />

        <div>
            <label class="text-[12px] font-medium text-slate-600 dark:text-slate-300 block mb-2">Role</label>
            <div class="nt-table-wrap nt-table-wrap-bare" style="max-height: 200px; overflow-y: auto;">
                <table class="nt-table nt-table-compact">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th class="nt-th-center">Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td class="text-[13px]">
                                    {{ $role->name }}
                                    @if ($role->slug === 'super_user')
                                        <span class="text-[11px] text-slate-400 ml-1">(Super User)</span>
                                    @endif
                                </td>
                                <td class="nt-text-center">
                                    <x-forms.checkbox name="role_ids[]" value="{{ $role->id }}"
                                        :checked="in_array($role->id, $selectedRoleIds)" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-[13px] text-slate-400 py-4">
                                    Belum ada role. Buat role terlebih dahulu.
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
            live-click="{{ $isEdit ? 'update' : 'store' }}" live-target="#user-panel" live-loading="#user-form-modal"
            data-nt-modal-close>
            Simpan
        </x-button>
    </x-slot:footer>
</x-modal.content>
