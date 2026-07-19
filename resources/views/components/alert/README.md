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

### Confirm Dialog — "Apakah kamu yakin?" (JS API, bukan Blade)

Dialog konfirmasi berdiri sendiri (tidak butuh blade shell modal terpisah), mengembalikan `Promise<boolean>`: `true` kalau user klik tombol konfirmasi, `false` kalau batal/klik backdrop/tekan Esc.

```js
const ok = await ntAlert.confirm({
    title: 'Hapus data?',
    message: `Data "${user.name}" akan dihapus permanen dan tidak bisa dikembalikan.`,
    tone: 'error',            // primary | accent | success | error | warning | info | neutral (default: warning)
    icon: null,               // class FontAwesome, default otomatis mengikuti tone
    confirmText: 'Ya, hapus', // default: 'Ya'
    cancelText: 'Batal',      // default: 'Batal'
    reverseButtons: false,    // true → tombol konfirmasi di kiri
});

if (ok) {
    await fetch(`/users/${user.id}`, { method: 'DELETE' });
    ntAlert.success('User berhasil dihapus.');
} else {
    ntAlert.info('Dibatalkan.');
}
```

Alias global tanpa prefix `ntAlert.`:

```js
if (await ntAlertConfirm({ message: 'Simpan perubahan?' })) {
    // lanjut simpan...
}
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
