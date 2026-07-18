# Netra UI — Tabs Navigation (Blade Component)

Komponen di `resources/views/components/tabs/`:

| File | Dipanggil sebagai | Peran |
|---|---|---|
| `index.blade.php` | `<x-tabs>` | Shell — membungkus `.nt-tabs` + `data-nt-tabs` + indicator. |
| `tab.blade.php` | `<x-tabs.tab>` | Satu tombol/link tab, ditaruh di `<x-slot:tabs>`. |
| `panel.blade.php` | `<x-tabs.panel>` | Satu panel konten, ditaruh di slot default. |

Satu mesin JS (`netra-tabs.js`) menangani 4 gaya visual (`folder`, `underline`, `pill`, `vertical`) × 4 mode URL (`href`, `hash`, `query`, `client`) — kombinasi bebas lewat prop `variant` dan `mode`.

## Instalasi

Pastikan `netra-tabs.css` dan `netra-tabs.js` sudah ter-load.

## Contoh Pakai

```blade
{{-- Underline, client-side (tanpa perubahan URL) --}}
<x-tabs variant="underline" mode="client" group="demo-underline">
    <x-slot:tabs>
        <x-tabs.tab value="overview" active>Overview</x-tabs.tab>
        <x-tabs.tab value="activity" badge="12">Activity</x-tabs.tab>
        <x-tabs.tab value="team">Team</x-tabs.tab>
    </x-slot:tabs>

    <x-tabs.panel value="overview">Ringkasan performa project minggu ini.</x-tabs.panel>
    <x-tabs.panel value="activity" hidden>12 aktivitas baru dari tim kamu.</x-tabs.panel>
    <x-tabs.panel value="team" hidden>Daftar anggota tim.</x-tabs.panel>
</x-tabs>

{{-- Pill, mode hash → URL jadi #demo-pill=... --}}
<x-tabs variant="pill" mode="hash" group="demo-pill">
    <x-slot:tabs>
        <x-tabs.tab value="daily" active>Daily</x-tabs.tab>
        <x-tabs.tab value="weekly">Weekly</x-tabs.tab>
    </x-slot:tabs>
    <x-tabs.panel value="daily">Statistik harian.</x-tabs.panel>
    <x-tabs.panel value="weekly" hidden>Rekap 7 hari terakhir.</x-tabs.panel>
</x-tabs>

{{-- Vertical rail, mode query → URL jadi ?tab=... --}}
<x-tabs variant="vertical" mode="query" group="demo-vertical" param="tab">
    <x-slot:tabs>
        <x-tabs.tab value="profile" icon="fa-regular fa-user" active>Profile</x-tabs.tab>
        <x-tabs.tab value="billing" icon="fa-regular fa-credit-card" badge="New">Billing</x-tabs.tab>
    </x-slot:tabs>
    <x-tabs.panel value="profile">Kelola nama, foto, dan info dasar akun kamu.</x-tabs.panel>
    <x-tabs.panel value="billing" hidden>Paket langganan & metode pembayaran.</x-tabs.panel>
</x-tabs>

{{-- Folder, mode href → tab = link asli, klik = navigasi penuh --}}
<x-tabs variant="folder" mode="href">
    <x-slot:tabs>
        <x-tabs.tab href="/dashboard" value="dashboard" icon="fa-solid fa-house">Dashboard</x-tabs.tab>
        <x-tabs.tab href="/forms" value="forms" icon="fa-solid fa-swatchbook">Forms</x-tabs.tab>
    </x-slot:tabs>
    <x-tabs.panel value="forms">Konten halaman ini.</x-tabs.panel>
</x-tabs>
```

## Props

**`<x-tabs>`**: `variant` (folder/underline/pill/vertical), `mode` (href/hash/query/client), `group` (string, wajib untuk mode hash/query), `orientation` (horizontal/vertical), `param` (nama query string custom).

**`<x-tabs.tab>`**: `value` (wajib), `active` (bool), `icon`, `badge`, `href` (untuk variant folder).

**`<x-tabs.panel>`**: `value` (wajib, harus cocok dengan tab pasangannya), `hidden` (bool — tandai semua panel non-aktif pertama).

## Catatan

Untuk konten yang dirender ulang lewat AJAX/Livewire, panggil ulang:
```js
window.NetraUI.initTabs(scopeEl);
```
