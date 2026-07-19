@php
    $isEdit = $user->exists;
@endphp

<x-modal.content title="{{ $isEdit ? 'Edit User' : 'Tambah User' }}"
    subtitle="{{ $isEdit ? $user->name : 'Buat akun user baru' }}" live-scope="Superuser.UserController">
    <div class="max-h-[70vh] overflow-y-auto pr-2">
        <div class="space-y-4">
            <x-forms.input type="hidden" name="id" label="" value="{{ $user->id }}" />

            {{-- Informasi Akun --}}
            <div>
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-circle-user text-primary-500 text-sm"></i>
                    <span class="text-[13px] font-semibold text-slate-700 dark:text-slate-200">Informasi Akun</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <x-forms.input name="name" label="Nama Lengkap" required value="{{ $user->name }}"
                        placeholder="mis. John Doe" />

                    <x-forms.input name="username" label="Username" value="{{ $user->username }}"
                        placeholder="Kosongkan untuk otomatis dari nama"
                        hint="Huruf kecil, angka, underscore. Otomatis jika dikosongkan." />
                </div>

                <div class="mt-3">
                    <x-forms.input type="email" name="email" label="Email" required value="{{ $user->email }}"
                        placeholder="user@example.com" />
                </div>
            </div>

            {{-- Keamanan --}}
            <div>
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-lock text-primary-500 text-sm"></i>
                    <span class="text-[13px] font-semibold text-slate-700 dark:text-slate-200">Keamanan</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <x-forms.input type="password" name="password"
                        label="{{ $isEdit ? 'Password (Kosongkan jika tidak diubah)' : 'Password' }}" :required="!$isEdit"
                        placeholder="Minimal 8 karakter" />

                    <x-forms.input type="password" name="password_confirmation" label="Konfirmasi Password"
                        :required="!$isEdit" placeholder="Ulangi password" />
                </div>
            </div>

            {{-- Profil & Status --}}
            <div>
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-id-card text-primary-500 text-sm"></i>
                    <span class="text-[13px] font-semibold text-slate-700 dark:text-slate-200">Profil & Status</span>
                </div>

                <x-forms.input name="avatar" label="URL Avatar" value="{{ $user->avatar }}"
                    placeholder="https://example.com/avatar.jpg" hint="Kosongkan untuk menggunakan avatar default" />

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <x-forms.toggle name="is_active" label="User Aktif" :checked="(bool) ($user->is_active ?? true)" />

                    <x-forms.select name="employee_id" label="Karyawan Terkait" placeholder="Cari & pilih karyawan…"
                        searchable :options="$employees->pluck('name', 'id')" :value="$user->employee_id" />
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Opsional. Hubungkan user dengan data karyawan yang sudah ada.
                </p>
            </div>

            {{-- Role Assignment --}}
            <div>
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-200 dark:border-slate-700">
                    <i class="fa-solid fa-shield-halved text-primary-500 text-sm"></i>
                    <span class="text-[13px] font-semibold text-slate-700 dark:text-slate-200">Role Assignment</span>
                </div>

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
                                        <div class="flex items-center gap-2">
                                            @if ($role->slug === 'super_user')
                                                <i class="fa-solid fa-crown text-amber-500 text-[11px]"></i>
                                            @else
                                                <i class="fa-solid fa-shield text-slate-400 text-[11px]"></i>
                                            @endif
                                            <span>{{ $role->name }}</span>
                                            @if ($role->slug === 'super_user')
                                                <span class="nt-badge nt-badge-warning text-[10px] px-1.5 py-0.5">Super
                                                    User</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="nt-text-center">
                                        <x-forms.checkbox name="role_ids[]" value="{{ $role->id }}"
                                            :checked="in_array($role->id, $selectedRoleIds)" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-[13px] text-slate-400 py-6">
                                        <div class="flex flex-col items-center gap-1">
                                            <i
                                                class="fa-solid fa-shield-slash text-xl text-slate-300 dark:text-slate-600"></i>
                                            <p>Belum ada role. Buat role terlebih dahulu.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <x-slot:footer>
            <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>
                <i class="fa-solid fa-xmark mr-1.5"></i>Batal
            </button>

            <x-button type="button" variant="primary" icon="fa-solid fa-floppy-disk"
                live-click="{{ $isEdit ? 'update' : 'store' }}" live-target="#user-panel" live-loading="#loading"
                live-callback-after="alert" data-nt-modal-close>
                {{ $isEdit ? 'Perbarui User' : 'Simpan User Baru' }}
            </x-button>
        </x-slot:footer>
    </div>
</x-modal.content>
