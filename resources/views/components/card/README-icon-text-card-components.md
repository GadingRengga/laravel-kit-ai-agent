# Netra UI — Icon, Text & Card Components (Laravel Blade)

Lanjutan dari paket button components. Berisi `<x-icon>`, `<x-text>`, dan
`<x-card>` beserta turunannya — setiap grup ada di folder sendiri, tidak ada
file lepas di luar folder.

## 1. Cara pasang

**Salin ke project Laravel kamu:**

```
resources/views/components/icon/*.blade.php   →  resources/views/components/icon/*.blade.php
resources/views/components/text/*.blade.php   →  resources/views/components/text/*.blade.php
resources/views/components/card/*.blade.php   →  resources/views/components/card/*.blade.php
public/assets/css/netra-icon.css              →  public/assets/css/netra-icon.css
public/assets/css/netra-card.css              →  public/assets/css/netra-card.css
public/assets/css/netra-typography.css        →  public/assets/css/netra-typography.css
```

Struktur folder (masing-masing grup satu folder, root tag pakai `index.blade.php`
— konvensi bawaan Blade untuk folder komponen):

```
resources/views/components/icon/
├── index.blade.php   → <x-icon>
└── tile.blade.php    → <x-icon.tile>

resources/views/components/text/
├── index.blade.php          → <x-text>
├── numeric.blade.php        → <x-text.numeric>
├── numeric-strong.blade.php → <x-text.numeric-strong>
└── metric.blade.php         → <x-text.metric>

resources/views/components/card/
├── index.blade.php       → <x-card>
├── statistic.blade.php   → <x-card.statistic>
└── placeholder.blade.php → <x-card.placeholder>
```

**Tambahkan 3 CSS baru ini di base layout** (setelah `netra-base.css`):

```html
<link rel="stylesheet" href="{{ asset('assets/css/netra-base.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/netra-icon.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/netra-card.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/netra-typography.css') }}">
```

Ketiganya file **baru** — tidak menimpa/mengedit file CSS tema yang sudah
kamu pasang. Aman ditambahkan kapan saja.

> **Bonus bugfix:** `netra-card.css` sekaligus memperbaiki `.stat-card` /
> `.stat-icon-wrap` / `.stat-icon` — class ini dipakai di
> `pages/admin-dashboard.html` bawaan tema tapi ternyata **tidak pernah
> didefinisikan** di CSS manapun (base, tables, atau monolith). Kalau kamu
> sudah sempat copy dashboard itu, kartu statistiknya sebelumnya tampil
> polos tanpa border/radius — sekarang otomatis kebenerin begitu file ini
> dimuat.

## 2. Komponen yang tersedia

| Komponen | Tag | Fungsi |
|---|---|---|
| `icon/index.blade.php` | `<x-icon>` | Ikon FontAwesome dengan size & warna konsisten |
| `icon/tile.blade.php` | `<x-icon.tile>` | Kotak ikon berwarna (soft/solid, size, circle) |
| `text/index.blade.php` | `<x-text>` | Teks dengan size/color/weight konsisten |
| `text/numeric.blade.php` | `<x-text.numeric>` | Angka rata kanan, font mono |
| `text/numeric-strong.blade.php` | `<x-text.numeric-strong>` | Angka rata kanan, tebal |
| `text/metric.blade.php` | `<x-text.metric>` | Skor/metrik berwarna (rating, growth %, dll) |
| `card/index.blade.php` | `<x-card>` | Kartu generik (header + body + footer) |
| `card/statistic.blade.php` | `<x-card.statistic>` | Kartu KPI/statistik dashboard |
| `card/placeholder.blade.php` | `<x-card.placeholder>` | Kartu "coming soon" |

> **Kenapa nama-nama ini berubah dari draft awal?** `box` → `tile`, `num`/`num-strong`
> → `numeric`/`numeric-strong`, `score` → `metric`, dan `stat` → `statistic`, supaya
> nama tag langsung menjelaskan fungsinya tanpa perlu buka isi filenya dulu.
> Class CSS internal (`nt-icon-box-*`, `nt-num`, `nt-score-*`, `.stat-card`) **tidak**
> ikut diubah — itu detail implementasi, dan `.stat-card` memang harus tetap sama
> karena itu literally nama class yang sudah dipakai di `admin-dashboard.html`.

## 3. `<x-icon>`

```blade
<x-icon name="trash-can" />                        {{-- fa-solid fa-trash-can, size md --}}
<x-icon name="trash-can" color="danger" />
<x-icon name="user" style="regular" size="lg" />
<x-icon name="github" style="brands" />
<x-icon name="spinner" spin />
<x-icon name="list" fixed-width />                 {{-- rata kiri seragam, cocok untuk list menu --}}
```

Dipakai gampang di dalam komponen lain juga:

```blade
<x-button.dropdown-item>
    <x-icon name="pencil" /> Edit
</x-button.dropdown-item>
```

## 4. `<x-icon.tile>`

```blade
<x-icon.tile name="arrow-trend-up" />                          {{-- soft primary, size md (default) --}}
<x-icon.tile name="users" variant="success" size="lg" />
<x-icon.tile name="bell" variant="danger" tone="solid" />
<x-icon.tile name="user" circle size="xl" />                   {{-- gaya avatar bulat --}}
```

Variant: `primary | success | danger | warning | info | neutral`
Tone: `soft` (default, bg tint) atau `solid` (bg penuh + ikon putih)
Size: `sm | md | lg | xl`

## 5. `<x-text>`

```blade
<x-text>Teks biasa</x-text>
<x-text size="2xl" weight="bold">$84.2k</x-text>
<x-text color="muted" size="sm">Deskripsi singkat di bawah judul</x-text>
<x-text color="danger" size="xs">Wajib diisi</x-text>
<x-text tag="label" for="email" weight="medium">Email</x-text>
<x-text truncate class="max-w-[200px]">Teks panjang yang akan dipotong…</x-text>
<x-text mono size="sm">INV-2024-00123</x-text>
```

Size: `2xs | xs | sm | base | lg | xl | 2xl`
Color: `strong | muted | faint | soft | accent | primary | success | danger | warning | info | white`
Weight: `normal | medium | semibold | bold`

### Sub-komponen tabel

```blade
<td class="px-4 py-3"><x-text.numeric>Rp 1.250.000</x-text.numeric></td>
<td class="px-4 py-3"><x-text.numeric-strong>Rp 12.400.000</x-text.numeric-strong></td>
<td class="px-4 py-3"><x-text.metric variant="success" bold>98.2</x-text.metric></td>
```

## 6. `<x-card>`

```blade
{{-- Sederhana --}}
<x-card title="Informasi Akun" description="Kelola data profil kamu di sini.">
    {{-- form dsb --}}
</x-card>

{{-- Dengan actions di header & footer --}}
<x-card title="Daftar Pengguna">
    <x-slot:actions>
        <x-button variant="primary" size="sm" icon="fa-solid fa-plus">Tambah</x-button>
    </x-slot:actions>

    {{-- tabel di sini --}}

    <x-slot:footer>
        <x-text size="xs" color="muted">Menampilkan 10 dari 240 data</x-text>
    </x-slot:footer>
</x-card>

{{-- Header custom penuh --}}
<x-card>
    <x-slot:header>
        <div class="flex items-center gap-2">
            <x-icon.tile name="chart-line" size="sm" />
            <x-text weight="semibold">Ringkasan Penjualan</x-text>
        </div>
    </x-slot:header>
    ...
</x-card>

{{-- Tanpa padding body, mis. untuk tabel full-width --}}
<x-card title="Data Tabel" :padding="false">
    <table class="nt-table">...</table>
</x-card>
```

## 7. `<x-card.statistic>` (KPI card, sekaligus bugfix)

```blade
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-card.statistic label="Revenue" value="$84.2k" icon="arrow-trend-up"
        trend="12.5%" trend-direction="up" trend-label="from last month" />

    <x-card.statistic label="Users" value="24,812" icon="users"
        trend="8.1%" trend-direction="up" trend-label="from last month" />

    <x-card.statistic label="Orders" value="1,453" icon="bag-shopping" variant="warning"
        trend="3.2%" trend-direction="down" trend-label="from last month" />

    <x-card.statistic label="Conversion" value="3.68%" icon="bullseye" variant="success"
        trend="0.4%" trend-direction="up" trend-label="from last month" />
</div>
```

## 8. `<x-card.placeholder>`

```blade
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <x-card.placeholder icon="chart-bar" style="regular"
        title="Chart content — Tahap 2" subtitle="Akan diisi pada tahap berikutnya"
        class="lg:col-span-2 min-h-72" />

    <x-card.placeholder icon="list-alt" style="regular"
        title="Activity Feed — Tahap 2" subtitle="Coming next stage"
        class="min-h-72" />
</div>
```

## 9. Kenapa strukturnya begini (catatan desain)

- **`<x-icon.tile>` menggantikan pola inline Tailwind yang tadinya ditulis
  ulang manual** tiap halaman (`w-12 h-12 rounded-xl bg-indigo-50
  dark:bg-indigo-900/30 flex items-center justify-center`) — sekarang satu
  komponen dengan varian warna yang selaras dengan button/chip
  (`primary/success/danger/warning/info/neutral`), dipakai konsisten di
  stat card, dropdown header, avatar, maupun placeholder card.
- **`<x-card.statistic>` sekaligus menambal bug nyata** — `.stat-card`,
  `.stat-icon-wrap`, `.stat-icon` dipakai di dashboard demo tapi CSS-nya
  memang tidak ada di manapun di source tema. `netra-card.css` mengisi
  definisi itu memakai token yang sama persis dengan `.comp-section`
  (`--nt-card-bg`, `--nt-card-border`, `--nt-card-radius`) supaya visualnya
  tetap konsisten dengan card lain, bukan warna/ukuran baru yang beda sendiri.
- **`<x-text>` tidak menciptakan skala warna baru** — semua opsi color
  memetakan ke class `nt-text-*` yang sudah ada di `netra-utilities.css`
  (strong/muted/faint/soft/accent/primary), ditambah beberapa yang belum
  ada (success/danger/warning/info/white/ukuran lg-xl-2xl) supaya lengkap
  tanpa keluar dari bahasa desain aslinya.
- **`<x-card>` murni pembungkus `.comp-section`** yang sudah dipakai
  konsisten di semua halaman demo tema — bukan sistem card baru — supaya
  kartu bikinanmu otomatis terasa "asli Netra UI", termasuk saat dark mode.
