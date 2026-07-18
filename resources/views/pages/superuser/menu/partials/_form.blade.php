@php
    $isEdit = $menu->exists;
@endphp

<x-modal.content title="{{ $isEdit ? 'Edit Menu' : 'Tambah Menu' }}"
    subtitle="{{ $isEdit ? $menu->name : 'Buat menu baru untuk sidebar' }}" live-scope="Superuser.MenuController">

    <div class="space-y-3">
        <x-forms.input type="hidden" name="id" label="" value="{{ $menu->id }}" />

        <x-forms.input name="name" label="Nama Menu" required value="{{ $menu->name }}"
            placeholder="mis. Manajemen User" />

        <x-forms.input name="slug" label="Slug" value="{{ $menu->slug }}"
            placeholder="Kosongkan untuk otomatis dari nama" hint="Huruf kecil, angka, strip/underscore." />

        {{--
            Sengaja TIDAK pakai prop :options di sini. Komponen <x-forms.select>
            bawaan kamu punya quirk: kalau key array numerik (kasus kita — id
            menu), value option-nya ketuker jadi ikut label
            ($value = is_int($value) ? $optLabel : $value;), jadi parent_id
            yang tersimpan bisa salah. Menulis <option> manual di slot (jalur
            fallback yang sudah didukung komponennya) supaya value tetap id asli.
        --}}
        <x-forms.select name="parent_id" label="Parent Menu">
            <option value="">— Tanpa parent (menu utama) —</option>
            @foreach ($parentOptions as $option)
                <option value="{{ $option['id'] }}" @selected((string) old('parent_id', $menu->parent_id) === (string) $option['id'])>
                    {{ $option['prefix'] }}{{ $option['name'] }}
                </option>
            @endforeach
        </x-forms.select>

        <div class="grid grid-cols-2 gap-3">
            <x-forms.input name="icon" label="Icon" value="{{ $menu->icon }}" placeholder="fa-solid fa-house"
                hint="Class Font Awesome." />
            <x-forms.input name="order" type="number" label="Urutan" value="{{ $menu->order ?? 0 }}" />
        </div>

        <x-forms.input name="route" label="Nama Route" value="{{ $menu->route }}" placeholder="mis. dashboard"
            hint="Nama route Laravel tujuan menu ini (opsional)." />

        <x-forms.toggle name="is_active" label="Menu Aktif" :checked="(bool) ($menu->is_active ?? true)" />
    </div>

    <x-slot:footer>
        <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>Batal</button>

        <x-button type="button" variant="primary" icon="fa-solid fa-floppy-disk"
            live-click="{{ $isEdit ? 'update' : 'store' }}" live-target="#menu-panel" live-loading="#menu-form-modal"
            data-nt-modal-close>
            Simpan
        </x-button>
    </x-slot:footer>
</x-modal.content>
