# Netra UI — CSS Modular Structure

CSS dipecah dari satu file besar (`netra-ui.css`, 4725 baris) menjadi 6 modul
agar setiap halaman hanya memuat CSS yang dibutuhkan.

## Daftar file & urutan load

| Urutan | File                  | Isi                                                                 | Wajib? |
|--------|-----------------------|----------------------------------------------------------------------|--------|
| 1      | `netra-base.css`      | CSS variables `--nt-*`, reset, scrollbar, layout shell (`#app`, `#sidebar`, `#main-col`), nav/submenu/active state, topbar, dropdown-panel, page-title-bar, comp-section (demo wrapper), brand-dot | **Ya, semua halaman** |
| 2      | `netra-forms.css`     | Form base components (`.form-input`, `.form-label`, dll), TomSelect, Datepicker, Timepicker, FileUpload, ColorPicker | Halaman dengan form |
| 3      | `netra-buttons.css`   | `.nt-btn-*`, `.nt-dropdown-*`, `.nt-chip-*`                          | Hampir semua halaman |
| 4      | `netra-modal.css`     | Modal component (`.nt-modal-*`, confirm dialog)                      | Halaman dengan modal |
| 5      | `netra-tables.css`    | Table core, premium table (tree/sticky/collapse), gantt, ultimate table | Halaman tabel |
| 6      | `netra-utilities.css` | Utility class (`.nt-text-*`, `.nt-bg-*`, `.nt-num`, dll)              | Halaman tabel/data |

## Dependency

- **`netra-base.css` harus selalu dimuat pertama** — semua modul lain
  bergantung pada CSS variables `--nt-*` yang didefinisikan di `:root`
  pada file ini.
- `netra-modal.css` punya tambahan `:root { --nt-modal-* }` sendiri, tidak
  bertabrakan dengan variabel di base.
- Urutan antar modul 2-6 tidak saling bergantung satu sama lain, jadi bebas
  diurutkan, tapi disarankan tetap pakai urutan di atas untuk konsistensi.

## Contoh penggunaan

### Halaman form wizard (tidak ada tabel)
```html
<link rel="stylesheet" href="netra-base.css" />
<link rel="stylesheet" href="netra-forms.css" />
<link rel="stylesheet" href="netra-buttons.css" />
<link rel="stylesheet" href="netra-modal.css" />
```

### Halaman tabel (basic/advance/premium/ultimate table)
```html
<link rel="stylesheet" href="netra-base.css" />
<link rel="stylesheet" href="netra-buttons.css" />
<link rel="stylesheet" href="netra-tables.css" />
<link rel="stylesheet" href="netra-utilities.css" />
```

### Halaman dashboard sederhana (cards, chip, button saja)
```html
<link rel="stylesheet" href="netra-base.css" />
<link rel="stylesheet" href="netra-buttons.css" />
```

## Catatan migrasi

- `netra-ui.js` **tidak berubah** — semua modul CSS tetap kompatibel dengan
  JS yang sama (sidebar toggle, dark mode, dropdown, datepicker, dll).
- Dua bug yang ditemukan saat pemecahan modul ini sudah diperbaiki:
  1. Duplikat rule `.nav-item.active` ("ACTIVE NAV STATE (refactored)")
     yang memakai `!important` + `transform: scale(1.05)` pada icon —
     rule ini **menimpa warna icon dark mode** dan menambah efek zoom
     yang tidak diinginkan. Sudah dihapus; `font-weight: 600` pada label
     aktif dipindahkan ke rule utama tanpa `!important`.
  2. Duplikat `.comp-section*`, `.page-title-bar`, `.nt-code`,
     `.nt-gantt-progress-fill`, `html.dark` (dead code dari proses
     "pindahkan dari xxx.html" sebelumnya) sudah dihapus.

## File asli

`netra-ui.css` (monolith, 4725 baris) tetap disediakan sebagai referensi /
fallback — bisa dipakai langsung kalau tidak mau pakai modul terpisah.

---

# Netra UI — JS Modular Structure

`netra-ui.js` (monolith, 2491 baris) juga dipecah jadi 4 modul dengan
mapping yang sama seperti CSS.

| Urutan | File                | Isi                                                                       | Wajib? |
|--------|---------------------|----------------------------------------------------------------------------|--------|
| 1      | `netra-base.js`     | Sidebar toggle, mobile sidebar, submenu, **initNavActive** (highlight menu aktif), topbar dropdown, dark mode toggle | **Ya, semua halaman** |
| 2      | `netra-forms.js`    | initSelect (TomSelect), initDatepicker, initTimepicker, initFileUpload, initColorPicker | Halaman dengan form |
| 3      | `netra-modal.js`    | ntModal (open/close/closeAll/closeOnBackdrop, Esc key)                     | Halaman dengan modal |
| 4      | `netra-tables.js`   | initTable, initTreeTable, initCollapseTable, initEditableTable, initGanttTable, initBasicTable, showToast, formatRupiah | Halaman tabel |

## Dependency & urutan load

- **`netra-base.js` harus selalu dimuat pertama** — modul lain bergantung
  pada `window.NetraUI` yang dibuat/digabung secara aman oleh tiap modul
  (`if (!window.NetraUI) window.NetraUI = {}` + `Object.assign`), jadi
  urutan 2-4 boleh bebas, tapi `initNavActive()` (di base) harus jalan agar
  sidebar menampilkan menu aktif dengan benar.
- Semua modul aman dimuat tanpa modul lain — tidak ada lagi
  `window.NetraUI = {...}` yang menimpa (overwrite) hasil modul lain.

## Bug yang ditemukan & diperbaiki saat pemecahan JS

1. **`initNavActive()` (highlight sidebar menu aktif saat load) hanya
   dipanggil dari modul tabel** — artinya halaman tanpa tabel (misal form
   wizard) **tidak pernah menjalankan highlight active menu saat
   pertama load**. Dipindahkan ke `netra-base.js`, dipanggil sekali via
   `DOMContentLoaded` di sana.
2. **`window.NetraUI = {...}` di `netra-forms.js` menimpa total** isi
   `window.NetraUI` — kalau `netra-modal.js`/`netra-tables.js` dimuat lebih
   dulu, hasil exportnya akan hilang tertimpa. Diganti jadi
   `Object.assign(window.NetraUI, {...})` dengan guard inisialisasi.
3. **Guard `if (window.NetraUI) {...}`** di `initTable`/`ntModal` export —
   kalau `netra-forms.js` tidak dimuat, `window.NetraUI` belum ada, jadi
   export gagal diam-diam. Diganti `if (!window.NetraUI) window.NetraUI = {}`.
4. Baris re-export dead code (`initSelect` di-reference dari
   `netra-tables.js` padahal fungsinya ada di modul forms) dihapus.

## Contoh penggunaan

### Halaman form wizard
```html
<script src="netra-base.js"></script>
<script src="netra-forms.js"></script>
<script src="netra-modal.js"></script>
```

### Halaman tabel
```html
<script src="netra-base.js"></script>
<script src="netra-tables.js"></script>
```

## File asli (JS)

`netra-ui.js` (monolith, 2491 baris) tetap disediakan dengan perbaikan bug
yang sama — bisa dipakai langsung kalau tidak mau pakai modul terpisah.

