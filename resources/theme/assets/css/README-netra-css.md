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
