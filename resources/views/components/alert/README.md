# Netra UI — Alert & Toast (Blade Component)

Komponen di `resources/views/components/alert/`:

| File | Dipanggil sebagai | Peran |
|---|---|---|
| `index.blade.php` | `<x-alert>` | Alert inline/banner — soft, solid, outline, glass, 7 tone warna. |
| `action.blade.php` | `<x-alert.action>` | Tombol aksi kecil di dalam `<x-slot:actions>` milik `<x-alert>`. |

Toast (notifikasi mengambang) **tidak dibuatkan Blade component** karena sifatnya dinamis/JS-driven — panggil langsung API `ntAlert.toast({...})`, `ntAlert.success()`, `.error()`, `.warning()`, `.info()`. Lihat `assets/js/netra-alerts.js`.

## Instalasi

Pastikan `netra-alerts.css` dan `netra-alerts.js` sudah ter-load di layout (di samping `netra-base.css/js`).

## Contoh Pakai

```blade
<x-alert tone="success" title="Pembayaran berhasil">
    Invoice #INV-2026-0417 telah lunas dan diproses.
</x-alert>

<x-alert tone="warning" mode="solid" title="Sesi akan berakhir">
    Kamu akan logout otomatis dalam 2 menit karena idle.
</x-alert>

<x-alert tone="error" title="3 item dipindahkan ke sampah">
    Item akan dihapus permanen setelah 30 hari.
    <x-slot:actions>
        <x-alert.action onclick="ntAlert.dismiss(this)">Urungkan</x-alert.action>
        <x-alert.action ghost onclick="ntAlert.dismiss(this)">Abaikan</x-alert.action>
    </x-slot:actions>
</x-alert>

<x-alert tone="primary" compact title="Nomor telepon terverifikasi" />

<x-alert tone="primary" banner icon="fa-solid fa-gift" title="Promo tahun baru — diskon 30%.">
    Berlaku sampai akhir bulan ini.
</x-alert>
```

### Toast (JS API langsung, bukan Blade)

```js
ntAlert.success('Data berhasil disimpan.', 'Berhasil');
ntAlert.error('Gagal mengunggah file. Coba lagi.', 'Terjadi kesalahan');
ntAlert.toast({ tone: 'accent', title: 'AI selesai memproses', message: 'Ringkasan laporan sudah siap.' });
ntAlert.clearAll();
```

## Props `<x-alert>`

| Prop | Tipe | Default | Keterangan |
|---|---|---|---|
| `tone` | string | `primary` | `primary \| accent \| success \| error \| warning \| info \| neutral` |
| `mode` | string | `soft` | `soft \| solid \| outline \| glass` |
| `title` | string\|null | `null` | Judul alert |
| `icon` | string\|null | otomatis per tone | Class FontAwesome |
| `dismissible` | bool | `true` | Tampilkan tombol close |
| `compact` | bool | `false` | Versi satu baris tanpa deskripsi |
| `banner` | bool | `false` | Versi full-width |
