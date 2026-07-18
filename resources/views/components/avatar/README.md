# Netra UI — Avatar (Blade Component)

Komponen di `resources/views/components/avatar/`:

| File | Dipanggil sebagai | Peran |
|---|---|---|
| `index.blade.php` | `<x-avatar>` | Avatar tunggal — inisial, gambar, atau ikon fallback. |
| `group.blade.php` | `<x-avatar.group>` | Avatar bertumpuk (stacked) untuk anggota tim. |
| `info.blade.php` | `<x-avatar.info>` | Baris avatar + nama + sub-label (tabel/list/card header). |
| `card.blade.php` | `<x-avatar.card>` | Kartu profil dengan banner, avatar, dan statistik. |
| `upload.blade.php` | `<x-avatar.upload>` | Avatar dengan overlay edit untuk upload foto profil. |

Fitur JS lanjutan pada halaman demo asli (**avatar carousel** & **avatar picker** multi/single-select) memakai markup khusus data-driven dan mesin `netra-avatar.js`; keduanya belum dibungkus jadi Blade component di paket ini — pakai langsung markup HTML dari `pages/avatar-components.html` kalau perlu, fungsinya sudah aktif otomatis karena JS-nya sudah ter-include.

## Instalasi

Pastikan `netra-avatar.css` dan `netra-avatar.js` sudah ter-load (untuk status badge pulse, tooltip grup, dan upload preview).

## Contoh Pakai

```blade
<x-avatar initials="GD" color="gradient-indigo" size="lg" status="online" />
<x-avatar src="https://api.dicebear.com/9.x/avataaars/svg?seed=Rani" size="xl" ring="indigo" />
<x-avatar icon="fa-regular fa-user" size="xl" />
<x-avatar loading size="md" />

<x-avatar.group :more="8">
    <x-avatar initials="GD" color="gradient-indigo" size="md" />
    <x-avatar src="https://api.dicebear.com/9.x/avataaars/svg?seed=Rani" size="md" />
</x-avatar.group>

<x-avatar.info name="Gading Devanta" sub="Administrator · Jakarta" status="online"
    initials="GD" color="gradient-indigo" />

<x-avatar.card name="Gading Devanta" role="Administrator" banner="indigo" status="online"
    initials="GD" color="gradient-indigo"
    :stats="['Posts' => 128, 'Followers' => '4.2k', 'Following' => 312]" />

<x-avatar.upload name="avatar" initials="GD" color="gradient-indigo" size="2xl" />
```

## Props `<x-avatar>`

| Prop | Tipe | Default | Keterangan |
|---|---|---|---|
| `src` | string\|null | `null` | URL gambar |
| `initials` | string\|null | `null` | Inisial teks |
| `icon` | string\|null | `null` | Class FontAwesome fallback |
| `size` | string | `md` | `xs \| sm \| md \| lg \| xl \| 2xl \| 3xl` |
| `shape` | string | `circle` | `circle \| square \| rounded` |
| `color` | string\|null | `null` | mis. `indigo`, `solid-slate`, `gradient-ocean` |
| `status` | string\|null | `null` | `online \| busy \| away \| offline` |
| `ring` | string\|null | `null` | `indigo \| success \| white` |
| `pulse` | bool | `false` | Animasi denyut "live" |
| `loading` | bool | `false` | Skeleton shimmer |

Warna gradient/solid/tint yang tersedia: lihat `netra-avatar.css` (class `nt-avatar-{color}`, `nt-avatar-solid-{color}`, `nt-avatar-gradient-{color}`).
