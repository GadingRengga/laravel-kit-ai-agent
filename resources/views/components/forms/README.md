# Netra UI — Form Blade Components

Komponen Blade siap pakai untuk semua form element di theme Netra UI kamu.
Sudah otomatis handle: `old()`, validation error (`$errors`), label, hint, required mark, dan pass-through atribut HTML (termasuk `wire:model` kalau pakai Livewire).

## Instalasi

1. Copy folder `resources/views/components/` ini ke project Laravel kamu (merge, jangan timpa kalau sudah ada komponen lain).
2. Pastikan `netra-base.css`, `netra-forms.css`, `netra-base.js`, `netra-forms.js`, dan TomSelect sudah ke-load di layout (sesuai yang kamu bilang sudah beres via Vite).
3. Selesai — tidak perlu registrasi apapun, semua otomatis ke-detect Laravel sebagai anonymous component (`<x-input>`, `<x-select>`, dst mengikuti nama file).

## Daftar Komponen

| Tag | File | Fungsi |
|---|---|---|
| `<x-input>` | input.blade.php | Text, email, password, number, dll + prefix/suffix/icon |
| `<x-textarea>` | textarea.blade.php | Multi-line + character counter |
| `<x-select>` | select.blade.php | Native select / TomSelect (searchable & multiple) |
| `<x-checkbox>` | checkbox.blade.php | Single checkbox / permission item |
| `<x-radio>` | radio.blade.php | Radio biasa |
| `<x-radio-card>` | radio-card.blade.php | Radio gaya card (pricing plan dsb) |
| `<x-toggle>` | toggle.blade.php | Switch on/off |
| `<x-datepicker>` | datepicker.blade.php | Vanilla JS calendar |
| `<x-timepicker>` | timepicker.blade.php | Vanilla JS time spinner |
| `<x-fileupload>` | fileupload.blade.php | Drag & drop upload |
| `<x-colorpicker>` | colorpicker.blade.php | Color picker (hasil tersimpan di hidden input) |

## Contoh Pakai

```blade
<form method="POST" action="/profile">
    @csrf

    <x-input name="name" label="Nama Lengkap" required placeholder="Nama kamu…" />

    <x-input name="email" type="email" label="Email" icon="fa-solid fa-envelope" required />

    <x-input name="password" type="password" label="Password" toggleable required />

    <x-input name="website" label="Website" prefix="https://" placeholder="domain.com" />

    <x-textarea name="bio" label="Bio" counter maxlength="300" hint="Maksimal 300 karakter." />

    <x-select name="category" label="Kategori" placeholder="Pilih kategori…" required
        :options="['electronics' => 'Electronics', 'fashion' => 'Fashion', 'sports' => 'Sports']" />

    <x-select name="roles" label="Role" searchable multiple :max-items="3"
        :options="['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer']" />

    <x-checkbox name="agree" value="1" label="Saya setuju dengan Syarat & Ketentuan" required />

    <x-radio name="gender" value="male" label="Laki-laki" checked />
    <x-radio name="gender" value="female" label="Perempuan" />

    <x-radio-card name="plan" value="starter" title="Starter" description="Rp 99k / bulan" checked />
    <x-radio-card name="plan" value="pro" title="Pro" description="Rp 299k / bulan" badge="Popular" />

    <x-toggle name="notifications" label="Email Notifications" checked />

    <x-datepicker name="birth_date" label="Tanggal Lahir" format="d/m/Y" required />

    <x-timepicker name="start_time" label="Jam Mulai" increment="15" />

    <x-fileupload name="avatar" label="Foto Profil" accept="image/*" max-size="2" />

    <x-colorpicker name="brand_color" label="Warna Brand" default="#2d5aff" />

    <button type="submit" class="btn btn-brand">Simpan</button>
</form>
```

## Catatan Penting

- **Error validasi otomatis**: kalau field ada di `$errors` bag (nama field sama dengan `name`), style otomatis berubah jadi `is-error` dan pesan error tampil di bawah input — tidak perlu kode tambahan.
- **`old()` otomatis**: setelah redirect back karena validasi gagal, value lama otomatis terisi (kecuali `type="password"`).
- **Livewire**: tinggal tambahkan `wire:model="field"` sebagai atribut biasa di komponen, contoh: `<x-input name="email" wire:model="email" label="Email" />`.
- **Konten dinamis (modal / Livewire re-render)**: kalau datepicker/timepicker/fileupload/colorpicker/tomselect dirender ulang lewat AJAX, panggil ulang initializer bawaan Netra UI supaya JS-nya aktif lagi:
  ```js
  window.NetraUI.initDatepicker(document);
  window.NetraUI.initTimepicker(document);
  window.NetraUI.initFileUpload(document);
  window.NetraUI.initColorPicker(document);
  window.NetraUI.initSelect(document); // TomSelect
  ```
- **Custom option `<select>`**: kalau tidak pakai prop `:options`, kamu tetap bisa tulis `<option>` manual di dalam slot:
  ```blade
  <x-select name="city" label="Kota">
      <option value="jkt">Jakarta</option>
      <option value="bdg">Bandung</option>
  </x-select>
  ```
