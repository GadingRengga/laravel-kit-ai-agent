# Netra UI — Modal Button + Shell + Content (Blade Component)

Tiga komponen di `resources/views/components/modal/`:

| File | Peran |
|---|---|
| `button.blade.php` | Tombol pemicu. Cuma "menunjuk" ke modal (shell) mana yang harus dibuka. Tidak menyentuh AJAX. |
| `shell.blade.php` | **Cangkang** — cuma `nt-modal-backdrop` + kotak dialog (ukuran & id). Statis, ditanam sekali di footer. Tidak tahu isinya apa. |
| `content.blade.php` | **Isi** modal sesungguhnya — `nt-modal-header` + `nt-modal-body` + `nt-modal-footer` sekaligus. Bisa ditulis statis di dalam `shell`, atau dirender terpisah di server lalu disuntikkan ke dalam `shell` lewat AJAX milikmu sendiri. |

Plus `resources/js/netra-modal-trigger.js` — jembatan tipis: delegasi klik, deteksi nested otomatis, override ukuran, tembak event `nt-modal:show`, lalu panggil `ntModal.open()` bawaan. **Tidak ada fetch/cache di dalamnya** — AJAX sepenuhnya kamu yang tulis.

## 1. Instalasi ke Vite

```js
// resources/js/app.js
import '../../assets/js/netra-modal';   // ntModal bawaan (wajib duluan)
import './netra-modal-trigger';         // file baru ini
```

## 2. Modal statis — shell dan content ditulis bersamaan

```blade
{{-- layouts/app.blade.php, sebelum </body> --}}
<x-modal.shell id="modal-info" size="sm">
    <x-modal.content id="modal-info" title="Info">
        Konten ditulis langsung di Blade, tidak lewat AJAX.

        <x-slot:footer>
            <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>Tutup</button>
        </x-slot:footer>
    </x-modal.content>
</x-modal.shell>

<x-modal.button target="modal-info">Buka Info</x-modal.button>
```

Perhatikan `id` di `<x-modal.shell>` dan `<x-modal.content>` **sama persis** — `shell` butuh itu untuk id backdrop, `content` butuh itu untuk wiring tombol close (`ntModal.close('{{ $id }}')`) dan `aria-labelledby`.

## 3. Modal dinamis — shell statis di footer, content menyusul lewat AJAX-mu

Cangkang ditanam kosong di footer (cuma id + ukuran):

```blade
<x-modal.shell id="modal-user-edit" size="md" />
```

Tombolnya, di tabel/list:

```blade
<x-modal.button target="modal-user-edit" onclick="loadUserEdit({{ $user->id }})">
    <i class="fa-solid fa-pen"></i> Edit
</x-modal.button>
```

Controller cukup `return view(...)` partial yang isinya `<x-modal.content>`:

```php
// routes/web.php
Route::get('/users/{user}/edit-form', [UserController::class, 'editForm'])->name('users.edit-form');
```

```php
public function editForm(User $user)
{
    return view('users.partials.edit-modal', compact('user'));
}
```

```blade
{{-- resources/views/users/partials/edit-modal.blade.php --}}
<x-modal.content id="modal-user-edit" title="Edit {{ $user->name }}">
    <div class="form-group">
        <label class="form-label">Nama</label>
        <input class="form-input" name="name" value="{{ $user->name }}">
    </div>

    <x-slot:footer>
        <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>Batal</button>
        <button type="submit" class="nt-btn nt-btn-primary" form="form-edit-user">Simpan</button>
    </x-slot:footer>
</x-modal.content>
```

JS-mu sendiri yang fetch lalu suntik hasilnya ke `[data-nt-modal-dialog]` milik shell:

```js
function loadUserEdit(id) {
    const dialog = document.querySelector('#modal-user-edit [data-nt-modal-dialog]');
    dialog.innerHTML = '<div class="nt-modal-body">Memuat...</div>';

    fetch(`/users/${id}/edit-form`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then((res) => res.text())
        .then((html) => { dialog.innerHTML = html; });
}
```

Karena hasil `fetch()` adalah output `<x-modal.content>` (header+body+footer sekaligus), sekali suntik langsung lengkap — tidak perlu isi bagian-bagian secara terpisah.

## 4. Props

### `<x-modal.button>`

| Prop | Default | Keterangan |
|---|---|---|
| `target` | — (wajib) | id `<x-modal.shell>` yang akan dibuka |
| `size` | `null` | override ukuran saat dibuka (`xs/sm/md/lg/xl/full`) |
| `nested` | `false` | paksa status nested (biasanya tidak perlu — auto terdeteksi) |

### `<x-modal.shell>`

| Prop | Default | Keterangan |
|---|---|---|
| `id` | — (wajib) | target dari `<x-modal.button>` |
| `size` | `md` | ukuran dialog |
| `nested` | `false` | tandai modal ini memang didesain sbg modal anak |

Slot default `<x-modal.shell>` = isi kotak dialog. Untuk modal statis, taruh `<x-modal.content>` di situ. Untuk modal dinamis, biarkan kosong (atau isi skeleton loading sendiri) — nanti ditimpa AJAX-mu.

### `<x-modal.content>`

| Prop | Default | Keterangan |
|---|---|---|
| `id` | — (wajib) | id shell yang sama, buat wiring tombol close & aria |
| `title` | `null` | judul header |
| `subtitle` | `null` | subjudul header |
| `close` | `true` | tampilkan tombol close bawaan di header |

Slot:
- default slot → isi `nt-modal-body`
- `<x-slot:footer>` → isi `nt-modal-footer` (kalau tidak diisi, footer tidak dirender)

## 5. Event `nt-modal:show` (opsional, kalau tidak mau pakai `onclick` manual)

Kalau kamu lebih suka logic AJAX terpusat (bukan `onclick` per tombol), `netra-modal-trigger.js` menembak event ini ke elemen `<x-modal.shell>` sebelum modal ditampilkan:

```js
document.getElementById('modal-user-edit').addEventListener('nt-modal:show', (e) => {
    const url = e.detail.trigger.dataset.url; // dari data-url yg kamu tempel sendiri di tombol
    const dialog = e.target.querySelector('[data-nt-modal-dialog]');
    fetch(url).then(r => r.text()).then(html => dialog.innerHTML = html);
});
```

```blade
<x-modal.button target="modal-user-edit" data-url="{{ route('users.edit-form', $user) }}">Edit</x-modal.button>
```

`e.detail`: `target` (id shell), `trigger` (elemen tombol yang diklik — baca `data-*` apapun yang kamu tempel sendiri), `isNested`, `depth`.

Dua-duanya (event ini atau `onclick` manual) valid — komponen tidak memaksa salah satu.

## 6. Skenario Modal Nested

Nested berarti: sebuah tombol **di dalam content yang sudah kamu suntik** membuka `<x-modal.shell>` lain.

```blade
{{-- masih di dalam edit-modal.blade.php --}}
<x-slot:footer>
    <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>Batal</button>
    <button type="submit" class="nt-btn nt-btn-primary" form="form-edit-user">Simpan</button>
    <x-modal.button target="modal-user-delete" class="nt-btn nt-btn-danger nt-btn-sm">
        Hapus akun ini
    </x-modal.button>
</x-slot:footer>
```

Cangkang modal keduanya, statis di footer layout:

```blade
<x-modal.shell id="modal-user-delete" size="xs">
    <x-modal.content id="modal-user-delete" title="Yakin hapus?">
        <x-slot:footer>
            <button type="button" class="nt-btn nt-btn-secondary" data-nt-modal-close>Batal</button>
            <button type="button" class="nt-btn nt-btn-danger" onclick="submitDeleteUser()">Ya, hapus</button>
        </x-slot:footer>
    </x-modal.content>
</x-modal.shell>
```

**Yang terjadi otomatis**, tanpa kode tambahan:

1. Klik tombol "Hapus akun ini" tetap tertangkap walau elemennya baru muncul dari `innerHTML =`, karena listener didelegasikan ke `document`, bukan di-bind per elemen.
2. Sebelum membuka `modal-user-delete`, script menghitung berapa modal `.nt-modal-open` lain yang masih terbuka (`modal-user-edit` masih terbuka) → otomatis menambah class `nt-modal-nested` pada shell-nya (backdrop lebih transparan, z-index lebih tinggi — sudah didefinisikan di `netra-modal.css` bawaan).
3. `ntModal` bawaan tetap yang mengurus stack (Esc menutup modal teratas dulu, body-scroll-lock, dst).
4. Kalau nesting lebih dari 2 level, z-index dinaikkan otomatis (`1000 + depth*50`).

Kalau kamu membuka modal anak lewat cara lain (`onclick="ntModal.open('modal-user-delete')"` manual, bukan lewat `<x-modal.button>`), deteksi otomatis di atas tidak jalan — tinggal tambahkan prop `nested` langsung di `<x-modal.shell nested>` supaya class nested tetap terpasang dari awal.

`data-nt-modal-close` di footer manapun (statis atau hasil AJAX-mu) didelegasikan otomatis — tombol tutup tidak perlu tahu id modalnya, tidak perlu inline `onclick="ntModal.close(...)"`.

## 7. Cangkang siap pakai (drop-in) untuk footer

Kalau tidak mau bikin `<x-modal.shell>` satu-satu per fitur, pakai partial siap pakai:

```
resources/views/layouts/partials/modal-shells.blade.php
```

Include sekali di layout utama, sebelum `</body>`:

```blade
@include('layouts.partials.modal-shells')
@vite(['resources/js/app.js'])
```

Isinya 2 cangkang generik yang dipakai ulang di seluruh halaman:

### `#app-modal` — cangkang umum untuk apa saja

Kosong, tinggal target dari tombol manapun + isi lewat AJAX-mu sendiri (lihat bagian 3 & 5 di atas untuk polanya):

```blade
<x-modal.button target="app-modal" size="md" onclick="loadUserEdit({{ $user->id }})">
    Edit
</x-modal.button>
```

Karena `#app-modal` dipakai ulang untuk banyak fitur, ukuran di-override per tombol lewat prop `size` — JS trigger otomatis mengganti class `nt-modal-{size}` tiap kali dibuka.

### `#app-modal-confirm` — cangkang konfirmasi generik, tinggal panggil

Sudah ada helper `ntConfirm()` di `resources/js/netra-modal-confirm.js` (tambahkan `import './netra-modal-confirm';` di `app.js`, setelah `netra-modal-trigger`). Tidak perlu bikin tombol/target manual — panggil langsung dari JS mana saja, termasuk dari dalam konten AJAX `#app-modal` yang sedang terbuka (nested otomatis, karena shell-nya sudah ditandai `nested` di Blade):

```js
ntConfirm({
    title: 'Hapus user?',
    message: `Data "${user.name}" akan dihapus permanen.`,
    confirmText: 'Ya, hapus',
    danger: true,
    onConfirm: () => {
        fetch(`/users/${user.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
            .then(() => location.reload());
    },
});
```

Kalau butuh lebih dari satu modal umum berjalan bersamaan (jarang terjadi), tinggal duplikasi pola di `modal-shells.blade.php` dengan `id` lain, mis. `app-modal-lg` untuk kebutuhan ukuran besar yang sering dipakai.
