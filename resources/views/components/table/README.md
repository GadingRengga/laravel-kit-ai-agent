# `<x-table>` — Netra UI Table Components

Kumpulan Blade component untuk semua varian tabel dari tema **Netra UI**
(default, striped, bordered, compact, responsive, sticky, sortable,
selectable, tree/hierarki, collapse/accordion, editable, gantt) — dibangun
komposisional, bukan 1 komponen monolith.

> **Filosofi:** 1 komponen kecil = 1 tanggung jawab. Komponen "shell"
> (`<x-table>`, `<x-table.tree>`, dst) menangani markup yang berulang
> (toolbar, thead wrapper, pagination). Isi kolom/cell tetap kamu tulis
> sendiri secara eksplisit — tidak ada magic yang menyembunyikan struktur
> data dari kamu.

---

## 1. Instalasi

1. Copy folder `components/` (isi `table.blade.php` + subfolder `table/`)
   ke `resources/views/components/`.
2. Copy `app-view-components/Table/Gantt.php` ke `app/View/Components/Table/Gantt.php`
   (satu-satunya komponen class-based, karena butuh kalkulasi tanggal).
3. Pastikan halaman yang memakai tabel sudah me-load asset tema, **urutan
   ini penting**:

   ```html
   <link rel="stylesheet" href="{{ asset('assets/css/netra-base.css') }}" />
   <link rel="stylesheet" href="{{ asset('assets/css/netra-buttons.css') }}" />
   <link rel="stylesheet" href="{{ asset('assets/css/netra-tables.css') }}" />
   <link rel="stylesheet" href="{{ asset('assets/css/netra-utilities.css') }}" />

   <script src="{{ asset('assets/js/netra-base.js') }}"></script>
   <script src="{{ asset('assets/js/netra-tables.js') }}"></script>
   ```

   `netra-base.css/js` **wajib dimuat pertama** — semua modul lain
   bergantung pada CSS variable `--nt-*` dan `window.NetraUI` yang
   diinisialisasi di sana.

4. Tidak ada registrasi tambahan di `AppServiceProvider` — Laravel
   otomatis mendeteksi komponen di `resources/views/components/` dan
   `app/View/Components/` berdasarkan konvensi penamaan.

Tidak ada dependency npm/composer tambahan selain yang sudah include di
Laravel (Carbon, dipakai oleh `Gantt.php`).

---

## 2. Struktur File

```
resources/views/components/
├── table.blade.php              ← shell utama (default/basic table)
└── table/
    ├── th.blade.php              ← header cell (sortable)
    ├── check-th.blade.php        ← header checkbox select-all
    ├── row.blade.php              ← <tr> baris
    ├── cell.blade.php             ← <td> sel data
    ├── check-cell.blade.php       ← <td> checkbox per baris
    ├── actions.blade.php          ← container tombol aksi
    ├── action.blade.php           ← 1 tombol aksi (detail/edit/hapus)
    │
    ├── tree.blade.php             ← [Tree]     shell tabel hierarki
    ├── tree-row.blade.php         ← [Tree]     baris level 1-3
    ├── tree-actions.blade.php     ← [Tree]     tombol expand/collapse all
    │
    ├── collapse.blade.php         ← [Collapse] shell tabel accordion
    ├── collapse-row.blade.php     ← [Collapse] baris utama (clickable)
    ├── expand-cell.blade.php      ← [Collapse] sel tombol chevron
    ├── detail-row.blade.php       ← [Collapse] baris detail (hidden)
    ├── detail-grid.blade.php      ← [Collapse] grid label-value
    ├── detail-item.blade.php      ← [Collapse] 1 pasang label-value
    │
    ├── editable.blade.php         ← [Editable] shell tabel inline-edit
    ├── editable-th.blade.php      ← [Editable] header + icon pensil
    ├── editable-row.blade.php     ← [Editable] baris (data-row-id)
    ├── editable-cell.blade.php    ← [Editable] sel yang bisa diklik-edit
    ├── status-cell.blade.php      ← [Editable] sel badge status auto-update
    │
    ├── gantt.blade.php            ← [Gantt]    view (dirender oleh Gantt.php)
    └── gantt-nav.blade.php        ← [Gantt]    tombol prev/next/label bulan

app/View/Components/Table/
└── Gantt.php                     ← [Gantt] class component (kalkulasi grid hari & posisi bar)
```

---

## 3. Komponen Inti (`<x-table>`)

Dipakai untuk varian default, striped, bordered, compact, responsive-card,
sticky-header, search, sort, select, pagination — semuanya lewat 1 shell
yang sama.

### Props `<x-table>`

| Prop          | Tipe      | Default              | Keterangan                                                              |
|---------------|-----------|-----------------------|--------------------------------------------------------------------------|
| `title`       | string    | `null`                | Judul di toolbar. Kosongkan → toolbar disembunyikan (kecuali ada `searchable`/`selectable`/slot `actions`) |
| `searchable`  | bool      | `false`               | Tampilkan search box di toolbar                                          |
| `selectable`  | bool      | `false`               | Tampilkan counter "X dipilih". Checkbox kolomnya tetap ditulis manual via `<x-table.check-th>` / `<x-table.check-cell>` |
| `striped`     | bool      | `false`               | `.nt-table-striped` — baris belang-belang                                |
| `bordered`    | bool      | `false`               | `.nt-table-bordered` — semua sel ada border                              |
| `compact`     | bool      | `false`               | `.nt-table-compact` — padding sel lebih rapat                            |
| `responsive`  | bool      | `false`               | `.nt-table-responsive-card` — baris jadi card di mobile (wajib isi `label=` di setiap `<x-table.cell>`) |
| `sticky`      | bool      | `false`               | Header tetap saat scroll (`.nt-table-sticky-head`)                       |
| `per-page`    | int       | `10`                  | Baris per halaman (pagination client-side oleh JS tema)                  |
| `pagination`  | bool      | `true`                | Tampilkan/sembunyikan footer pagination (mis. `false` utk mini table widget dashboard) |
| `wrap-class`  | string    | `nt-table-wrap-bare`  | Ganti ke `nt-table-wrap-flush` kalau toolbar disembunyikan               |

### Slot

| Slot        | Wajib? | Keterangan                                      |
|-------------|--------|--------------------------------------------------|
| `head`      | ya     | Isi `<tr>` di `<thead>` — kombinasi `<x-table.th>` / `<x-table.check-th>` / `<th>` biasa |
| `actions`   | tidak  | Tombol di toolbar (kanan, sebelah search box)     |
| default     | ya     | Baris `<tbody>` — kombinasi `<x-table.row>` + `<x-table.cell>` |

### Sub-komponen

**`<x-table.th col="" sortable default="asc|desc" align="left|center|right">`**
Header dengan icon sort. `col` = key yang dikirim ke `data-nt-sort-col`
(harus sama persis dengan yang dipakai di `data-sort-value` pada cell-nya).
Tanpa prop `sortable`, dia jadi `<th>` biasa.

**`<x-table.check-th />`**
Header checkbox "select all". Selalu dipasangkan dengan `<x-table.check-cell />` di setiap baris.

**`<x-table.row clickable>`**
`<tr>`. `clickable` menambah class `cursor-pointer` (cocok kalau baris punya `wire:click`/`onclick` buka detail).

**`<x-table.cell label="" sort-value="" align="left|center|right">`**
`<td>`. `label` dipakai sebagai heading saat mode `responsive` aktif di mobile
(samakan dengan teks header kolom). `sort-value` **hanya diisi kalau konten
cell bukan teks polos** (badge, avatar+nama, tanggal terformat) — isi versi
mentah yang dipakai JS untuk sorting.

**`<x-table.check-cell />`**
`<td>` checkbox per baris.

**`<x-table.actions align="end|start|center">` + `<x-table.action icon="" title="" href="" danger>`**
Container tombol aksi. `action` render sebagai `<a>` kalau ada `href`, kalau
tidak jadi `<button>`. Semua attribute lain (`wire:click`, `wire:confirm`,
`onclick`, dst) otomatis diteruskan.

### Contoh dasar

```blade
<x-table title="Data Pengguna" searchable selectable per-page="8">
    <x-slot:actions>
        <button class="nt-btn nt-btn-primary nt-btn-sm"><i class="fa-solid fa-plus"></i> Tambah</button>
    </x-slot:actions>

    <x-slot:head>
        <x-table.check-th />
        <x-table.th col="name" sortable default="asc">Nama</x-table.th>
        <x-table.th col="email" sortable>Email</x-table.th>
        <x-table.th align="right">Aksi</x-table.th>
    </x-slot:head>

    @foreach ($users as $user)
        <x-table.row clickable>
            <x-table.check-cell />
            <x-table.cell label="Nama" :sort-value="strtolower($user->name)">{{ $user->name }}</x-table.cell>
            <x-table.cell label="Email">{{ $user->email }}</x-table.cell>
            <x-table.cell>
                <x-table.actions>
                    <x-table.action icon="fa-regular fa-pen-to-square" title="Edit" href="{{ route('users.edit', $user) }}" />
                    <x-table.action icon="fa-regular fa-trash-can" title="Hapus" danger
                        wire:click="delete({{ $user->id }})" wire:confirm="Yakin hapus?" />
                </x-table.actions>
            </x-table.cell>
        </x-table.row>
    @endforeach
</x-table>
```

### Varian modifier (tinggal tambah prop)

```blade
<x-table striped>...</x-table>
<x-table bordered>...</x-table>
<x-table compact>...</x-table>
<x-table responsive>...</x-table>            {{-- wajib isi label= di setiap cell --}}
<x-table sticky>...</x-table>
<x-table :pagination="false">...</x-table>   {{-- mini table widget, tanpa footer --}}
```

---

## 4. Tree Table (hierarki)

Untuk struktur parent-child yang bisa expand/collapse (organisasi, kategori
bertingkat, dll). Mendukung 3 level kedalaman.

### `<x-table.tree id="tree-table">`

| Prop | Default        | Keterangan                     |
|------|----------------|----------------------------------|
| `id` | `tree-table`   | Dipakai sebagai target `data-target` oleh `tree-actions` |

Slot `head` untuk header, default slot untuk baris (`<x-table.tree-row>`).

### `<x-table.tree-actions target="#tree-table">`
Sepasang tombol "Expand All" / "Collapse". `target` = CSS selector ke
`<x-table.tree>` (pakai `#` + id-nya).

### `<x-table.tree-row>`

| Prop         | Default | Keterangan                                                      |
|--------------|---------|-------------------------------------------------------------------|
| `node-id`    | —       | ID unik node (wajib)                                              |
| `parent`     | `null`  | `node-id` milik parent — wajib diisi kalau `level > 1`             |
| `level`      | `1`     | `1`, `2`, atau `3` — menentukan indentasi & style baris             |
| `label`      | —       | Teks nama node                                                    |
| `icon`       | `null`  | Class font-awesome, mis. `fa-solid fa-folder-open`. Kosongkan utk leaf tanpa icon |
| `expandable` | `true`  | `false` = leaf node (anggota tim), tombol toggle diganti spacer     |
| `expanded`   | `true`  | Menentukan icon `+`/`-` — merepresentasikan state **children milik baris ini**, bukan dirinya sendiri |
| `hidden`     | `false` | `true` = baris disembunyikan saat load (baru muncul saat parent-nya di-expand) |

Kolom-kolom lain (Kepala, Anggaran, dst) ditulis manual di slot default
pakai `<x-table.cell>`, karena kontennya bebas (progress bar, badge, dst).

```blade
<div class="comp-section-header flex items-center justify-between">
    <p class="comp-section-title">Struktur Organisasi</p>
    <x-table.tree-actions target="#tree-table" />
</div>

<x-table.tree id="tree-table">
    <x-slot:head>
        <th class="nt-min-w-260">Divisi / Tim / Anggota</th>
        <th>Kepala</th>
        <th>Anggaran</th>
    </x-slot:head>

    <x-table.tree-row node-id="div-tech" level="1" icon="fa-solid fa-folder-open" label="Divisi Teknologi">
        <x-table.cell>Andi R.</x-table.cell>
        <x-table.cell class="font-mono text-[12.5px]">Rp 2.400Jt</x-table.cell>
    </x-table.tree-row>

    <x-table.tree-row node-id="tm-eng" parent="div-tech" level="2" icon="fa-solid fa-folder-open" label="Tim Engineering">
        <x-table.cell>Budi S.</x-table.cell>
        <x-table.cell class="font-mono text-[12.5px]">Rp 900Jt</x-table.cell>
    </x-table.tree-row>

    <x-table.tree-row node-id="mb-fe" parent="tm-eng" level="3" :expandable="false" icon="fa-regular fa-user" label="Frontend Dev">
        <x-table.cell>Citra L.</x-table.cell>
        <x-table.cell class="font-mono text-[12.5px]">Rp 350Jt</x-table.cell>
    </x-table.tree-row>
</x-table.tree>
```

> Kalau data hierarki berasal dari Eloquent (self-relation `parent_id`),
> render pakai recursive `@include`/Blade component biasa di level aplikasi
> — bukan tanggung jawab komponen ini untuk menebak struktur pohon dari data.

---

## 5. Collapse Table (accordion detail)

Baris utama bisa diklik untuk menampilkan baris detail di bawahnya (cocok
untuk order list, invoice, dll).

### `<x-table.collapse id="collapse-table">`
Shell, sama seperti `tree`. Slot `head` + default slot.

### `<x-table.collapse-row target="detail-row-1">`
Baris utama. `target` = `id` (tanpa `#`) dari `<x-table.detail-row>` pasangannya.

### `<x-table.expand-cell target="detail-row-1">`
Sel pertama berisi tombol chevron. `target` harus sama dengan
`collapse-row` di atasnya. Biasanya jadi kolom pertama, sebelum
`<x-table.cell>` lainnya.

### `<x-table.detail-row id="detail-row-1" colspan="5">`
Baris detail, tersembunyi secara default. **`colspan` wajib diisi manual**
= total jumlah kolom di thead (sengaja tidak dihitung otomatis, supaya
komponen tidak perlu "mengintip" slot lain — 1 angka yang jarang berubah).

### `<x-table.detail-grid>` + `<x-table.detail-item label="...">`
Helper untuk grid label-value di dalam detail row.

```blade
<x-table.collapse id="collapse-table">
    <x-slot:head>
        <th class="nt-w-30"></th>
        <th>Order ID</th>
        <th>Pelanggan</th>
        <th>Total</th>
        <th>Status</th>
    </x-slot:head>

    @foreach ($orders as $order)
        <x-table.collapse-row target="detail-row-{{ $order->id }}">
            <x-table.expand-cell target="detail-row-{{ $order->id }}" />
            <x-table.cell class="font-mono text-[12px] nt-text-primary">#{{ $order->code }}</x-table.cell>
            <x-table.cell class="font-medium">{{ $order->customer_name }}</x-table.cell>
            <x-table.cell class="font-mono">Rp {{ number_format($order->total, 0, ',', '.') }}</x-table.cell>
            <x-table.cell><span class="nt-badge nt-badge-info">{{ $order->status_label }}</span></x-table.cell>
        </x-table.collapse-row>

        <x-table.detail-row id="detail-row-{{ $order->id }}" colspan="5">
            <x-table.detail-grid>
                <x-table.detail-item label="No. HP">{{ $order->phone }}</x-table.detail-item>
                <x-table.detail-item label="Alamat">{{ $order->address }}</x-table.detail-item>
                <x-table.detail-item label="Kurir">{{ $order->courier }}</x-table.detail-item>
            </x-table.detail-grid>
        </x-table.detail-row>
    @endforeach
</x-table.collapse>
```

---

## 6. Editable Table (inline cell edit)

Kolom tertentu bisa diklik langsung untuk edit inline (perubahan disimpan
di sisi client oleh JS tema — untuk persist ke server, dengarkan event
custom yang di-dispatch JS atau sinkronkan manual, lihat catatan di §8).

### `<x-table.editable>`

| Prop              | Default             | Keterangan                                                                 |
|-------------------|----------------------|-------------------------------------------------------------------------------|
| `id`              | `editable-table`     | ID tabel                                                                       |
| `currency-prefix` | `Rp `                | Prefix format currency                                                        |
| `name-field`      | `name`               | Field yang dipakai JS sebagai "nama entitas" di notifikasi                    |
| `entity-label`    | `Item`               | Label entitas, mis. "Produk"                                                  |
| `status-field`    | `null`               | Field yang dipakai utk hitung status badge otomatis, mis. `stock`             |
| `status-rules`    | `[]`                 | **Array PHP biasa** (bukan string JSON!) — otomatis di-`json_encode()` oleh komponen |

```php
'status-rules' => [
    ['max' => 0,      'label' => 'Habis',     'cls' => 'nt-badge-danger',  'color' => '#DC2626'],
    ['max' => 4,      'label' => 'Low Stock',  'cls' => 'nt-badge-warning','color' => '#D97706'],
    ['max' => 999999, 'label' => 'Aktif',      'cls' => 'nt-badge-success','color' => ''],
],
```

### `<x-table.editable-th editable>`
Header kolom. Prop `editable` (bool) menambahkan icon pensil sebagai
penanda visual kolom yang bisa diedit.

### `<x-table.editable-row :row-id="$product->id">`
Baris, otomatis nambah `data-nt-row` + `data-row-id`.

### `<x-table.editable-cell field="" :value="" type="text|number" format="currency|integer">`
Sel yang bisa diklik-edit. `value` = nilai mentah (bukan yang sudah
diformat) — konten slot tetap yang menentukan tampilan awal (biasanya versi
terformat).

### `<x-table.status-cell>`
Sel badge status yang kontennya di-refresh otomatis oleh JS setiap kali
field terkait (`status-field`) diedit.

```blade
<x-table.editable
    id="editable-table"
    entity-label="Produk"
    status-field="stock"
    :status-rules="[
        ['max' => 0, 'label' => 'Habis', 'cls' => 'nt-badge-danger', 'color' => '#DC2626'],
        ['max' => 4, 'label' => 'Low Stock', 'cls' => 'nt-badge-warning', 'color' => '#D97706'],
        ['max' => 999999, 'label' => 'Aktif', 'cls' => 'nt-badge-success', 'color' => ''],
    ]"
>
    <x-slot:head>
        <th class="col-check"><input type="checkbox" class="form-checkbox" /></th>
        <x-table.editable-th editable>Nama Produk</x-table.editable-th>
        <x-table.editable-th editable>Harga</x-table.editable-th>
        <x-table.editable-th editable>Stok</x-table.editable-th>
        <th>Status</th>
        <th class="nt-th-right">Aksi</th>
    </x-slot:head>

    @foreach ($products as $product)
        <x-table.editable-row :row-id="$product->id">
            <td class="col-check"><input type="checkbox" class="form-checkbox"></td>

            <x-table.editable-cell field="name" :value="$product->name">{{ $product->name }}</x-table.editable-cell>

            <x-table.editable-cell field="price" :value="$product->price" type="number" format="currency" class="font-mono">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </x-table.editable-cell>

            <x-table.editable-cell field="stock" :value="$product->stock" type="number" format="integer" class="font-mono">
                {{ $product->stock }}
            </x-table.editable-cell>

            <x-table.status-cell>
                <span class="nt-badge nt-badge-{{ $product->status_variant }}">{{ $product->status_label }}</span>
            </x-table.status-cell>

            <td>
                <x-table.actions>
                    <x-table.action icon="fa-regular fa-trash-can" title="Hapus" danger data-nt-row-delete />
                </x-table.actions>
            </td>
        </x-table.editable-row>
    @endforeach
</x-table.editable>
```

---

## 7. Gantt Table

Satu-satunya komponen **class-based** (`App\View\Components\Table\Gantt`),
karena butuh kalkulasi tanggal (grid hari per bulan, posisi & lebar bar
dari `start`/`end`).

### `<x-table.gantt>`

| Prop            | Tipe      | Default          | Keterangan                                                              |
|-----------------|-----------|-------------------|----------------------------------------------------------------------------|
| `tasks`         | array     | —                 | Lihat format di bawah                                                     |
| `month`         | string    | bulan ini (`Y-m`) | Bulan yang tampil pertama                                                 |
| `months-around` | int       | `0`               | Render N bulan sebelum & sesudah `month` sekaligus (utk navigasi prev/next tanpa reload halaman) |

**Format 1 item `tasks`:**

```php
[
    'label'     => 'Netra UI Design System', // nama task/proyek
    'pic'       => 'Eka N.',                  // opsional
    'start'     => '2026-06-01',              // parsable Carbon::parse()
    'end'       => '2026-06-20',               // opsional jika 'milestone' => true
    'progress'  => 85,                         // opsional, 0-100
    'color'     => 'indigo',                   // sesuai class .nt-gantt-bar-{color} di netra-tables.css
    'milestone' => false,                      // true = tampil sbg diamond di 1 hari (pakai 'start')
]
```

### `<x-table.gantt-nav />`
Tombol prev/next + label bulan aktif, ditaruh **di luar** `<x-table.gantt>`
(biasanya di `comp-section-header`) — JS tema mencarinya otomatis di
elemen sekitar.

```blade
<div class="comp-section-header flex items-center justify-between">
    <p class="comp-section-title">Project Roadmap</p>
    <x-table.gantt-nav />
</div>

<x-table.gantt
    month="2026-06"
    months-around="1"
    :tasks="[
        ['label' => 'Netra UI Design System', 'pic' => 'Eka N.', 'start' => '2026-06-01', 'end' => '2026-06-20', 'progress' => 85, 'color' => 'indigo'],
        ['label' => 'API Gateway v2', 'pic' => 'Rian S.', 'start' => '2026-06-10', 'end' => '2026-06-25', 'progress' => 40, 'color' => 'green'],
        ['label' => 'Rilis Beta', 'pic' => 'Tim Produk', 'start' => '2026-06-30', 'milestone' => true, 'color' => 'amber'],
    ]"
/>
```

> **Penting:** lebar kolom hari di-hardcode `28px` di `Gantt.php`
> (`DAY_WIDTH`) supaya perhitungan lebar bar akurat. Kalau kamu ubah CSS
> `.gcell-day { width: ... }` di `netra-tables.css`, update juga konstanta
> ini agar bar tidak meleset.

---

## 8. Kenapa arsitekturnya begini?

- **Dipisah per fitur, bukan per "tingkat".** Nama halaman demo tema
  (basic/advance/premium/ultimate) itu urutan showcase, bukan batas
  arsitektur. Semua varian sebenarnya berbagi 1 markup inti
  (`.nt-table-wrap > .nt-table`) + lapisan fitur di atasnya — jadi
  komponennya dipecah per fitur (`th`, `cell`, `tree-row`, `editable-cell`,
  dst), lalu di-reuse di semua varian.
- **Eksplisit, bukan magic.** Checkbox kolom tidak otomatis muncul walau
  `selectable="true"`; `colspan` di detail-row tidak dihitung otomatis;
  konten cell (badge, avatar, progress bar) selalu kamu tulis sendiri.
  Ini supaya komponen tidak pernah "menebak" struktur data kamu secara
  keliru, dan supaya HTML yang di-generate tetap bisa ditebak dari kode
  Blade-nya saja.
- **`status-rules` sebagai array PHP, bukan JSON mentah** — komponen yang
  menangani `json_encode()`, kamu tidak perlu menulis/escape JSON manual
  di dalam attribute HTML.
- **Client-side, bukan server-side.** Search/sort/pagination di varian
  dasar & editable table dijalankan oleh JS tema di browser (data harus
  sudah ada semua di DOM saat load) — bukan query ulang ke server per
  klik. Cocok untuk dataset kecil-menengah (puluhan-ratusan baris). Kalau
  butuh server-side (query per halaman dari database, dataset besar),
  itu perlu layer tambahan (misal Livewire) — lihat §9.

---

## 9. Batasan & Pengembangan Lanjutan

- **Belum ada mode server-side pagination/sorting.** JS tema
  (`netra-tables.js`) murni client-side — cocok untuk starter kit skala
  kecil-menengah. Kalau butuh sorting/search/pagination langsung dari
  Eloquent (dataset besar), komponen ini perlu dibungkus ulang dengan
  Livewire (ganti `data-nt-*` triggers dengan `wire:model`/`wire:click`,
  dan render ulang query per request).
- **Editable table menyimpan perubahan di client saja** (state di memori
  JS, hilang saat reload). Untuk persist ke database, perlu dengarkan
  event dari `netra-tables.js` (belum ada event dispatch keluar — saat
  ini `initEditableTable` menyimpan ke variabel internal) atau tambahkan
  `wire:change`/fetch manual di `editable-cell`.
- **Gantt belum mendukung drag-resize bar** (ubah durasi task dengan drag)
  — versi saat ini render read-only berdasarkan data yang dikirim server.
- Kalau butuh salah satu dari ini, bilang saja — bisa dikembangkan
  berikutnya tanpa mengubah API komponen yang sudah ada (menambah prop
  baru, bukan mengganti yang lama).
