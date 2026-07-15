@php
    $isEdit = $user->exists;
@endphp

<x-modal.content title="Atur Role — {{ $user->name }}" subtitle="Pilih satu atau lebih role yang dimiliki user ini"
    live-scope="Superuser.UserRoleController">

    <input type="hidden" name="user_id" value="{{ $user->id }}">

    <div>
        <label class="text-[12px] font-medium text-slate-600 dark:text-slate-300 block mb-2">Role</label>
        <div class="nt-table-wrap nt-table-wrap-bare" style="max-height: 300px; overflow-y: auto;">
            <table class="nt-table nt-table-compact">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Deskripsi</th>
                        <th class="nt-th-center">Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td class="text-[13px] font-medium text-slate-700 dark:text-slate-200">{{ $role->name }}
                            </td>
                            <td class="text-[12px] text-slate-400">{{ $role->description ?: '—' }}</td>
                            <td class="nt-text-center">
                                <x-forms.checkbox name="role_ids[]" value="{{ $role->id }}" :checked="in_array($role->id, $selectedRoleIds)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-[13px] text-slate-400 py-4">
                                Belum ada role. Buat role dulu lewat Manajemen Role.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>Batal</button>

        <x-button type="button" variant="primary" icon="fa-solid fa-floppy-disk" live-click="update"
            live-target="#user-role-panel" live-loading="#user-role-form-modal" data-nt-modal-close>
            Simpan
        </x-button>
    </x-slot:footer>
</x-modal.content>
