# Netra UI — Form Wizard (Blade Component)

Dua komponen di `resources/views/components/wizard/`:

| File | Dipanggil sebagai | Peran |
|---|---|---|
| `index.blade.php` | `<x-wizard>` | Root. Membungkus `.nt-wizard` + data-* attribute, opsional jadi `<form>` kalau diberi `action`. |
| `step.blade.php` | `<x-wizard.step>` | Satu step. Statis (isi langsung) atau lazy-AJAX (kasih `url`). |

Mesin navigasinya (pindah step tanpa reload, validasi, progress bar, persist ke sessionStorage, dst) **sudah ada di tema** — `assets/js/netra-form-wizard.js` + `netra-form-wizard.css`. Komponen ini tidak menduplikasi itu, cuma membungkusnya dengan DX Blade yang lebih enak.

Tambahan baru dari saya:
- `resources/js/netra-form-wizard-lazy.js` — addon kecil, opsional, untuk step yang mau di-lazy-load lewat AJAX.
- `resources/css/netra-wizard-lazy.css` — skeleton/error state untuk step yang sedang di-lazy-load.

## 1. Instalasi ke Vite

```js
// resources/js/app.js
import '../../assets/js/netra-form-wizard';  // mesin wizard bawaan tema (wajib duluan)
import './netra-form-wizard-lazy';           // addon lazy-step (opsional, lewati kalau semua step statis)
```

```js
// vite.config.js — kalau pakai lazy-step, ikutkan CSS-nya
input: [
  'resources/css/app.css',
  'resources/css/netra-wizard-lazy.css',
  'resources/js/app.js',
],
```

## 2. Pemakaian dasar (semua step statis, tanpa AJAX sama sekali)

```blade
<x-wizard style="horizontal" action="{{ route('users.store') }}">
    <x-wizard.step title="Akun" subtitle="Informasi dasar" icon="fa-solid fa-user">
        <div class="form-group">
            <label class="form-label">Nama</label>
            <input class="form-input" name="name" required>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-input" type="email" name="email" required>
        </div>
    </x-wizard.step>

    <x-wizard.step title="Profil" subtitle="Detail tambahan" icon="fa-solid fa-id-card" optional>
        <div class="form-group">
            <label class="form-label">Bio</label>
            <textarea class="form-textarea" name="bio"></textarea>
        </div>
    </x-wizard.step>

    <x-wizard.step title="Review" subtitle="Periksa kembali" icon="fa-solid fa-check-double">
        <div class="nt-wizard-review-group">
            <div class="nt-wizard-review-item">
                <span>Nama</span>
                <strong data-review-field="name"></strong>
            </div>
        </div>
    </x-wizard.step>
</x-wizard>
```

Karena `action` diisi, root-nya otomatis jadi `<form>` (+ `@csrf`), tombol "Simpan" di step terakhir = `type="submit"` asli — submit native ke server, tidak perlu JS tambahan sama sekali. Head (step indicator) dan foot (tombol Sebelumnya/Selanjutnya/Simpan) **dibuat otomatis** oleh JS, tidak perlu ditulis manual.

Kalau `action` dikosongkan, root jadi `<div>` biasa — cocok kalau submission mau ditangani sendiri lewat JS (lihat bagian 5).

## 3. Dua mode step: statis vs lazy-AJAX (per-step, bukan global)

Kamu tanya soal 2 mode — saya sengaja buat **per-step**, bukan satu saklar global di `<x-wizard>`, supaya satu wizard boleh campur:

```blade
<x-wizard style="vertical" action="{{ route('orders.store') }}">

    {{-- Step statis biasa — langsung tampil, tidak ada request apapun --}}
    <x-wizard.step title="Info Pelanggan" icon="fa-solid fa-user">
        <input class="form-input" name="customer_name" required>
    </x-wizard.step>

    {{-- Step lazy-AJAX — baru di-fetch begitu user PERTAMA KALI sampai
         di step ini (bukan saat wizard pertama dimuat), hasilnya
         di-cache di DOM, tidak fetch ulang kalau balik lagi ke step ini --}}
    <x-wizard.step title="Pilih Produk" icon="fa-solid fa-box" url="{{ route('orders.product-step') }}" />

    <x-wizard.step title="Ringkasan" icon="fa-solid fa-check">
        ...
    </x-wizard.step>

</x-wizard>
```

Kenapa per-step lebih masuk akal dibanding flag `mode="ajax"` global di root:
- Step pertama/kedua yang ringan (1-2 input) tidak ada untungnya di-AJAX-kan — cuma nambah 1 request + delay yang gak perlu.
- Step yang berat (misal daftar produk 500 baris dari server, select2 dependent-dropdown, dsb) baru masuk akal di-lazy-load supaya HTML awal wizard tetap ringan.
- Kalau dipaksa jadi 1 saklar global, kamu jadi harus pilih: AJAX-kan semua step (termasuk yang ringan, boros request) atau statis semua (step berat ikut nge-blok initial load). Per-step, kamu pilih sendiri per kasus.

Controller untuk step AJAX cukup `return view(...)` partial berisi field-field-nya saja (tanpa `<section>` pembungkus — itu sudah disediakan `<x-wizard.step>`):

```php
Route::get('/orders/product-step', fn () => view('orders.partials.product-step'));
```

```blade
{{-- resources/views/orders/partials/product-step.blade.php --}}
<div class="form-group">
    <label class="form-label">Produk</label>
    <select class="form-select" name="product_id" required>
        @foreach($products as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
        @endforeach
    </select>
</div>
```

Yang terjadi otomatis (`netra-form-wizard-lazy.js`):
1. Step tampil dulu dengan skeleton (bawaan `<x-wizard.step url="...">` kalau slot kosong).
2. Begitu step itu aktif (baik dari klik "Selanjutnya" atau langsung step pertama kalau dia yang punya `url`), fetch jalan otomatis, hasil disuntik ke `[data-step-mount]`.
3. `data-step-loaded="true"` dipasang, jadi balik-balik ke step ini lagi tidak fetch ulang.
4. Validasi (`data-validate="true"` di root) tetap jalan normal karena field hasil AJAX sudah ada di DOM saat tombol Next/Submit ditekan — asal user memang sempat mengunjungi step itu.

### Guard: submit diblokir kalau ada step wajib yang belum ke-load

Kalau `allowSkip` / `allowStepClick` dipakai, secara teori user bisa lompat langsung ke step terakhir tanpa pernah mampir ke step ber-AJAX — field di step itu jadi belum pernah ada di DOM, sehingga validasi core tidak akan pernah menganggapnya kosong/invalid (karena field-nya memang belum ada), padahal seharusnya wajib diisi. **Ini sudah ditangani otomatis oleh `netra-form-wizard-lazy.js`**, tidak perlu setup tambahan:

- Setiap klik tombol Submit, addon mengecek semua `<x-wizard.step url="...">` yang **bukan** `optional`.
- Kalau ada yang `data-step-loaded` masih `false`, submit **dibatalkan total** (baik jalur native `<form>` maupun jalur JS `nt-wizard:submit`), user otomatis dilempar balik ke step tersebut, muncul peringatan singkat, dan fetch step itu langsung dipicu.
- Step yang ditandai `optional` (`<x-wizard.step optional url="...">`) dikecualikan dari guard ini — konsisten dengan cara core wizard mengecualikan step optional dari validasi wajib.

Jadi kombinasi lazy-AJAX + `allowSkip`/`allowStepClick` aman dipakai bersamaan tanpa risiko data wajib ke-skip.

## 4. Tampilan lebih dari satu (horizontal / vertical / minimal) — tetap 1 komponen

Kamu juga tanya apakah tampilan wizard (ada beberapa gaya visual) perlu dipisah jadi beberapa komponen. **Tidak** — cukup 1 prop `style`:

```blade
<x-wizard style="horizontal">...</x-wizard>
<x-wizard style="vertical">...</x-wizard>
<x-wizard style="minimal">...</x-wizard>
```

Alasannya: markup `<x-wizard.step>` **identik** untuk ketiga gaya — yang beda cuma cara header/step-indicator dirender (list horizontal di atas, list vertikal di samping, atau progress bar minimal), dan itu sudah ditangani otomatis oleh `netra-form-wizard.js` lewat `data-style`. Kalau dipisah jadi `<x-wizard-horizontal>`, `<x-wizard-vertical>`, `<x-wizard-minimal>`, kamu cuma menduplikasi root wrapper yang sama persis tanpa manfaat, dan developer harus hafal 3 nama komponen untuk 1 konsep yang sama. Satu komponen + 1 prop lebih konsisten dengan pola `<x-modal.shell size="...">` yang sudah kita buat sebelumnya.

## 5. Submission via JS/AJAX sendiri (tanpa `action`)

```blade
<x-wizard style="horizontal" id="wizard-checkout">
    ...
</x-wizard>
```

```js
document.getElementById('wizard-checkout').addEventListener('nt-wizard:submit', (e) => {
    const { formData } = e.detail; // sudah di-serialize semua field ber-name di seluruh step
    fetch('/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(formData),
    }).then(...);
});
```

## 6. Props

### `<x-wizard>`

| Prop | Default | Keterangan |
|---|---|---|
| `style` | `horizontal` | `horizontal` \| `vertical` \| `minimal` |
| `validate` | `true` | validasi field `required`/`data-required` sebelum pindah step |
| `allowStepClick` | `false` | klik langsung di step header untuk pindah |
| `allowSkip` | `false` | boleh lompat step tanpa validasi |
| `persist` | `false` | simpan progress ke sessionStorage |
| `persistKey` | `null` | key custom untuk persist |
| `scrollTop` | `true` | auto-scroll ke atas wizard saat ganti step |
| `showStepCount` | `true` | tampilkan "Langkah 1 dari 4" (gaya minimal) |
| `labelPrev` / `labelNext` / `labelSubmit` | `Sebelumnya` / `Selanjutnya` / `Simpan` | label tombol |
| `action` | `null` | isi untuk submit native (`<form>` + `@csrf`); kosongkan untuk submission via JS |
| `method` | `POST` | dipakai kalau `action` diisi; otomatis `@method(...)` kalau bukan GET/POST |

Slot: default = isi dengan `<x-wizard.step>`. `<x-slot:footer>` opsional untuk footer custom (tombol wajib pakai `data-wizard-prev` / `data-wizard-next` / `data-wizard-submit` supaya tetap terhubung ke mesin navigasi).

### `<x-wizard.step>`

| Prop | Default | Keterangan |
|---|---|---|
| `title` | — (wajib) | judul step |
| `subtitle` | `null` | deskripsi singkat |
| `icon` | `null` | class FontAwesome |
| `optional` | `false` | badge "Opsional", dikecualikan dari validasi wajib |
| `url` | `null` | isi untuk mode lazy-AJAX; kosongkan untuk step statis |

## 8. Kompatibilitas dengan LiveDomJs / tool SPA-DOM-swap lainnya

Kalau tema-mu jalan bareng [LiveDomJs](https://gadingrengga.github.io/LiveDomJs) (atau tool sejenis: htmx, Turbo, custom fetch+innerHTML) yang mengganti sebagian `<body>` tanpa reload penuh (`live-target`, `live-spa-region`), ada satu hal yang perlu diperhatikan:

- **`<x-modal.*>` — aman tanpa syarat apapun.** `ntModal.open()`/`close()` selalu `document.getElementById()` ulang tiap dipanggil, tidak menyimpan referensi node, tidak butuh init sekali di awal. Modal yang barusan disuntik oleh LiveDomJs langsung bisa dibuka/ditutup normal.

- **`<x-wizard>` — butuh 1 langkah tambahan.** `netra-form-wizard.js` cuma `initWizard()` sekali saat `DOMContentLoaded`. Kalau ada `<x-wizard>` baru masuk ke DOM lewat `live-target`/`live-spa-region` **setelah** load awal, dia tidak otomatis dapat head/foot/navigasi.

Solusinya, tambahkan satu file addon lagi (kali ini pakai event resmi LiveDomJs, bukan tebak-tebak lagi):

```js
// resources/js/app.js — urutan import PENTING, ini paling terakhir
import '../../assets/js/netra-form-wizard';
import './netra-form-wizard-lazy';       // kalau pakai step ajax
import './netra-form-wizard-autoinit';   // WAJIB kalau ada LiveDomJs
```

LiveDomJs menembak event `live-dom:afterUpdate` ke `document` setiap kali selesai memperbarui DOM — baik lewat navigasi `live-spa-region` maupun update `live-target` biasa. File addon ini cukup dengar event itu lalu panggil ulang `initWizard()`/`initWizardLazy()`:

```js
document.addEventListener('live-dom:afterUpdate', function () {
    window.NetraUI.initWizard(document);
    window.NetraUI.initWizardLazy(document);
});
```

Keduanya sudah idempotent (wizard yang sudah aktif tidak di-reset/kehilangan step yang sedang berjalan), jadi aman dipanggil berkali-kali termasuk untuk region yang sama sekali tidak berisi wizard.

## 9. Referensi cepat data/event bawaan tema (tidak berubah)

Semua ini tetap berlaku apa adanya dari `netra-form-wizard.js` — lihat komentar di kepala file itu untuk detail lengkap:

- Review step otomatis: `data-review-field="nama_field"` di elemen manapun dalam wizard, otomatis terisi nilai field `name="nama_field"`.
- Event: `nt-wizard:init`, `nt-wizard:step-change`, `nt-wizard:validate`, `nt-wizard:submit` (+ `nt-wizard:step-loaded` dari addon lazy kita).
- API JS: `window.NetraUI.wizardGoto(el, index)`, `wizardNext(el)`, `wizardPrev(el)`.
