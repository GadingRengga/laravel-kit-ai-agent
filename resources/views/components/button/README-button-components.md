# Netra UI — Button Components (Laravel Blade)

Paket komponen Blade untuk seluruh sistem "Button & Dropdown" tema Netra UI:
button, button group, split button, dropdown, dan chip/tag.

## 1. Cara pasang

**Salin folder ke project Laravel kamu:**

```
resources/views/components/button/*.blade.php →  resources/views/components/button/*.blade.php
public/assets/js/netra-dropdown.js            →  public/assets/js/netra-dropdown.js
public/assets/js/netra-chip.js                →  public/assets/js/netra-chip.js
```

Semua file button (termasuk `<x-button>` sendiri) ada di dalam **satu folder**
`components/button/` — tidak ada file lepas di luar folder, biar rapi:

```
resources/views/components/button/
├── index.blade.php             → <x-button>
├── group.blade.php             → <x-button.group>
├── split.blade.php             → <x-button.split>
├── dropdown.blade.php          → <x-button.dropdown>
├── dropdown-header.blade.php   → <x-button.dropdown-header>
├── dropdown-section.blade.php  → <x-button.dropdown-section>
├── dropdown-item.blade.php     → <x-button.dropdown-item>
├── dropdown-divider.blade.php  → <x-button.dropdown-divider>
└── chip.blade.php              → <x-button.chip>
```

(Laravel otomatis mengenali `button/index.blade.php` sebagai `<x-button>` —
konvensi "index component" bawaan Blade untuk folder komponen.)

(Sesuaikan path `public/assets/js` kalau di project kamu asset tema disimpan di
lokasi lain, mis. `public/vendor/netra-ui/js`.)

**Tambahkan 2 script baru ini di base layout**, setelah `netra-base.js` dan
`netra-buttons.css` / `netra-tables.css`:

```html
<link rel="stylesheet" href="{{ asset('assets/css/netra-buttons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/netra-tables.css') }}"> {{-- varian soft/outline-*/icon/pill/loading ada di sini, quirk bawaan tema --}}

<script src="{{ asset('assets/js/netra-base.js') }}"></script>
<script src="{{ asset('assets/js/netra-dropdown.js') }}"></script>
<script src="{{ asset('assets/js/netra-chip.js') }}"></script>
```

> Kenapa perlu `netra-dropdown.js` baru? Demo asli (`button-components.html`)
> menjalankan dropdown/split-button pakai `id` manual + `onclick="ntDdToggle('id', this)"`
> yang ditulis inline di tiap halaman — tidak reusable kalau dipakai lewat komponen
> Blade (id gampang bentrok, apalagi di dalam `@foreach` baris tabel). Modul baru ini
> 100% berbasis `data-*` attribute, jadi `<x-button.dropdown>` aman dipakai berkali-kali
> di halaman yang sama tanpa perlu mikirin id sama sekali — termasuk otomatis re-init
> untuk konten yang di-render Livewire/AJAX lewat `window.NetraUI.initDropdown(root)`.

## 2. Komponen yang tersedia

| Komponen | Tag | Fungsi |
|---|---|---|
| `button/index.blade.php` | `<x-button>` | Tombol inti — semua varian, size, icon, loading, badge |
| `button/group.blade.php` | `<x-button.group>` | Segmented control / view switcher |
| `button/split.blade.php` | `<x-button.split>` | Aksi utama + dropdown toggle |
| `button/dropdown.blade.php` | `<x-button.dropdown>` | Dropdown generik, trigger bebas |
| `button/dropdown-header.blade.php` | `<x-button.dropdown-header>` | Header info di dropdown |
| `button/dropdown-section.blade.php` | `<x-button.dropdown-section>` | Bungkus grup item |
| `button/dropdown-item.blade.php` | `<x-button.dropdown-item>` | Satu baris item menu |
| `button/dropdown-divider.blade.php` | `<x-button.dropdown-divider>` | Garis pemisah grup |
| `button/chip.blade.php` | `<x-button.chip>` | Label/tag kecil |

## 3. Referensi cepat `<x-button>`

```blade
<x-button>Simpan</x-button>

{{-- Varian --}}
<x-button variant="primary">Primary</x-button>
<x-button variant="secondary">Secondary</x-button>
<x-button variant="success">Success</x-button>
<x-button variant="danger">Danger</x-button>
<x-button variant="warning">Warning</x-button>
<x-button variant="info">Info</x-button>
<x-button variant="outline">Outline Primary</x-button>
<x-button variant="outline-success">Outline Success</x-button>
<x-button variant="soft-primary">Soft Primary</x-button>
<x-button variant="ghost">Ghost</x-button>
<x-button variant="ghost-primary">Ghost Primary</x-button>

{{-- Ukuran --}}
<x-button size="sm">Small</x-button>
<x-button size="lg">Large</x-button>
<x-button size="xl">Extra Large</x-button>

{{-- Ikon --}}
<x-button icon="fa-solid fa-check">Simpan</x-button>
<x-button icon-trailing="fa-solid fa-arrow-right">Lanjut</x-button>
<x-button icon-only icon="fa-solid fa-pencil" variant="ghost" title="Edit" />

{{-- Pill & badge --}}
<x-button pill icon-only icon="fa-solid fa-heart" variant="soft-primary" title="Like" />
<x-button icon="fa-regular fa-bell" :badge="4">Notifikasi</x-button>

{{-- Loading & disabled --}}
<x-button loading loading-text="Menyimpan…">Simpan</x-button>
<x-button disabled>Tidak aktif</x-button>

{{-- Sebagai link --}}
<x-button href="/export" icon-trailing="fa-solid fa-download">Export</x-button>

{{-- Livewire --}}
<x-button wire:click="save" wire:loading.attr="disabled" wire:target="save">
    <span wire:loading.remove wire:target="save">Simpan</span>
    <span wire:loading wire:target="save" class="flex items-center gap-2">
        <span class="nt-btn-spinner"></span> Menyimpan…
    </span>
</x-button>
```

## 4. Button group

```blade
<x-button.group>
    <x-button variant="primary">Grid</x-button>
    <x-button variant="secondary">List</x-button>
    <x-button variant="secondary">Table</x-button>
</x-button.group>
```

## 5. Split button

```blade
<x-button.split variant="primary" icon="fa-solid fa-floppy-disk" wire:click="save">
    Simpan

    <x-slot:menu>
        <x-button.dropdown-section>
            <x-button.dropdown-item icon="fa-solid fa-floppy-disk" wire:click="save">
                Simpan
            </x-button.dropdown-item>
            <x-button.dropdown-item icon="fa-solid fa-clone" wire:click="saveAs">
                Simpan sebagai…
            </x-button.dropdown-item>
        </x-button.dropdown-section>
    </x-slot:menu>
</x-button.split>
```

## 6. Dropdown generik (mis. menu akun di topbar)

```blade
<x-button.dropdown align="right">
    <x-slot:trigger>
        <x-button variant="secondary" icon="fa-solid fa-user-gear" icon-trailing="fa-solid fa-chevron-down">
            Akun
        </x-button>
    </x-slot:trigger>

    <x-button.dropdown-header :title="auth()->user()->name" :sub="auth()->user()->email" />
    <x-button.dropdown-section>
        <x-button.dropdown-item icon="fa-regular fa-user" href="{{ route('profile.edit') }}">
            Profil Saya
        </x-button.dropdown-item>
        <x-button.dropdown-item icon="fa-solid fa-gear" href="{{ route('settings') }}">
            Pengaturan
        </x-button.dropdown-item>
    </x-button.dropdown-section>
    <x-button.dropdown-divider />
    <x-button.dropdown-section>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button.dropdown-item icon="fa-solid fa-right-from-bracket" danger
                onclick="event.preventDefault(); this.closest('form').submit();" href="#">
                Sign out
            </x-button.dropdown-item>
        </form>
    </x-button.dropdown-section>
</x-button.dropdown>
```

**Pola row-action di tabel** (icon-only trigger, align kanan, buka ke atas kalau di baris bawah):

```blade
@foreach ($users as $user)
    <tr>
        {{-- ... --}}
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1">
                <x-button variant="ghost" size="sm" icon-only icon="fa-solid fa-pencil" title="Edit" />
                <x-button.dropdown align="right">
                    <x-slot:trigger>
                        <x-button variant="ghost" size="sm" icon-only icon="fa-solid fa-ellipsis-vertical" title="Menu" />
                    </x-slot:trigger>
                    <x-button.dropdown-section>
                        <x-button.dropdown-item icon="fa-solid fa-pencil" href="{{ route('users.edit', $user) }}">
                            Edit
                        </x-button.dropdown-item>
                        <x-button.dropdown-item icon="fa-solid fa-trash-can" danger
                            wire:click="delete({{ $user->id }})" keep-open>
                            Hapus
                        </x-button.dropdown-item>
                    </x-button.dropdown-section>
                </x-button.dropdown>
            </div>
        </td>
    </tr>
@endforeach
```

Karena dropdown-nya berbasis `data-*` (bukan `id`), aman dipakai di dalam
`@foreach` — tidak akan ada dropdown baris lain yang ikut kebuka.

## 7. Dropdown check-list (mis. "Urutkan")

```blade
<x-button.dropdown>
    <x-slot:trigger>
        <x-button variant="soft-primary" icon="fa-solid fa-arrow-up-wide-short"
            icon-trailing="fa-solid fa-chevron-down">
            Urutkan
        </x-button>
    </x-slot:trigger>

    <x-button.dropdown-header title="Urutan" />
    <x-button.dropdown-section>
        <x-button.dropdown-item :checked="true">Terbaru</x-button.dropdown-item>
        <x-button.dropdown-item :checked="false">Terlama</x-button.dropdown-item>
        <x-button.dropdown-item :checked="false">A–Z</x-button.dropdown-item>
        <x-button.dropdown-item :checked="false">Z–A</x-button.dropdown-item>
    </x-button.dropdown-section>
</x-button.dropdown>
```

Klik salah satu item otomatis menandai yang lain nonaktif — sudah ditangani
`netra-dropdown.js`, tanpa perlu JS tambahan di halaman kamu.

## 8. Chip / Tag

```blade
<x-button.chip variant="success">Aktif</x-button.chip>
<x-button.chip variant="danger" dot>Ditolak</x-button.chip>
<x-button.chip variant="primary" icon="fa-solid fa-tag">Primary</x-button.chip>

{{-- Removable, contoh dipakai lepas dari list --}}
@foreach ($tags as $tag)
    <x-button.chip variant="neutral" removable wire:key="tag-{{ $tag->id }}">
        {{ $tag->name }}
    </x-button.chip>
@endforeach
```

## 9. Kenapa strukturnya begini (catatan desain)

- **Semua varian CSS 1:1 dengan class asli tema** (`.nt-btn-primary`, `.nt-btn-outline-success`,
  `.nt-dd-item-danger`, dst) — tidak ada CSS baru yang ditulis, komponen ini murni
  "pembungkus" markup supaya kamu tidak perlu hafal/copy-paste class panjang tiap saat.
- **`iconOnly` otomatis mengisi `aria-label`** dari `title` kalau kamu belum kasih —
  tema aslinya tidak eksplisit soal aksesibilitas untuk tombol icon-only, ini
  tambahan kecil supaya screen reader tetap bisa baca fungsi tombol.
- **`<x-button.split>` meneruskan semua atribut ke tombol utama**, bukan wrapper-nya —
  supaya `wire:click`/`onclick` yang kamu tulis di `<x-button.split ...>` langsung
  nempel ke aksi utamanya, bukan ke tombol toggle chevron.
- **Dropdown tertutup otomatis tiap item diklik**, kecuali kamu tandai
  `keep-open` — ini mencegah 1 baris kode ekstra `ntDdToggle(...)` yang di demo asli
  harus ditulis manual tiap tombol.
