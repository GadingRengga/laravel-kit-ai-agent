# Simple LiveDOM Loading & Alert API

Panduan singkat untuk menggunakan loading dan alert dengan LiveDOM secara seragam di seluruh aplikasi.

## 🚀 Quick Start

### Loading

```html
<!-- Loading untuk elemen tertentu -->
<button live-click="save" live-loading="#card-container">Simpan Data</button>

<!-- Loading dengan pesan custom -->
<button
    live-click="save"
    live-loading="#card-container"
    data-loading-message="Menyimpan perubahan..."
>
    Simpan Data
</button>

<!-- Full-page loading (tanpa target) -->
<button live-click="save" live-loading>Simpan Data</button>
```

### Alert

```html
<!-- Alert setelah aksi selesai -->
<button live-click="delete" live-callback-after="showSuccessAlert">
    Hapus Data
</button>
```

```javascript
// Di layout atau script
function showSuccessAlert() {
    ntAlert.success("Data berhasil dihapus", "Berhasil");
}
```

---

## 📦 Yang Sudah Disetup

### 1. Global Loading Overlay

Lokasi: `layouts/app.blade.php` (line 37-42)

```html
<div id="nt-loading-overlay" class="nt-loading-overlay" style="display: none;">
    <div class="nt-loading-card">
        <span class="nt-spinner-dual nt-spin-lg"></span>
        <p class="nt-loading-text" data-nt-loading-text>Memuat…</p>
    </div>
</div>
```

### 2. Global Alert Container

Lokasi: `layouts/app.blade.php` (line 45)

```html
<div id="nt-alert-container" class="nt-alert-container"></div>
```

### 3. Auto-init Handler

Lokasi: `layouts/app.blade.php` (line 160-180)

```javascript
// Simple LiveDOM Loading Handler
function handleLiveLoading(container) {
    const loadingEl = container.querySelector("[live-loading]");
    if (!loadingEl) return;

    const targetId = loadingEl.getAttribute("live-loading");
    const message = loadingEl.getAttribute("data-loading-message") || "Memuat…";

    if (targetId) {
        ntLoading.showIn(targetId, message);
    } else {
        ntLoading.show(message);
    }
}

// Simple LiveDOM Callback After Handler
function handleLiveCallbackAfter(container) {
    const callbackEl = container.querySelector("[live-callback-after]");
    if (!callbackEl) return;

    const callbackName = callbackEl.getAttribute("live-callback-after");
    if (callbackName && typeof window[callbackName] === "function") {
        window[callbackName]();
    }
}
```

Handler ini otomatis dipanggil di setiap `live-dom:afterUpdate` dan `live-dom:afterSpa`.

---

## 🎯 Cara Pakai

### Loading

#### 1. Loading untuk elemen tertentu (contained)

```html
<div id="my-card" class="relative">
    <!-- Konten card -->
    <button live-click="update" live-loading="#my-card">Update</button>
</div>
```

#### 2. Loading dengan pesan custom

```html
<button
    live-click="upload"
    live-loading="#upload-area"
    data-loading-message="Mengunggah file..."
>
    Upload
</button>
```

#### 3. Full-page loading

```html
<button live-click="process" live-loading>Proses Data</button>
```

### Alert

#### 1. Success Alert

```javascript
function showSuccess() {
    ntAlert.success("Data berhasil disimpan", "Berhasil");
}
```

```html
<button live-click="save" live-callback-after="showSuccess">Simpan</button>
```

#### 2. Error Alert

```javascript
function showError() {
    ntAlert.error("Gagal menghapus data", "Error");
}
```

#### 3. Warning Alert

```javascript
function showWarning() {
    ntAlert.warning("Sesi akan berakhir dalam 2 menit", "Peringatan");
}
```

#### 4. Info Alert

```javascript
function showInfo() {
    ntAlert.info("Data berhasil diperbarui", "Info");
}
```

#### 5. Custom Toast

```javascript
function showCustomToast() {
    ntAlert.toast({
        tone: "success",
        title: "Backup Selesai",
        message: "3.2GB berhasil dicadangkan.",
        duration: 4000,
        position: "top-right",
    });
}
```

---

## 🔧 Helper Functions (Opsional)

Buat helper di `resources/js/app.js` untuk kemudahan:

```javascript
// Simple alert helpers
window.showAlert = {
    success: (message, title) => ntAlert.success(message, title),
    error: (message, title) => ntAlert.error(message, title),
    warning: (message, title) => ntAlert.warning(message, title),
    info: (message, title) => ntAlert.info(message, title),
};

// Simple loading helpers
window.showLoading = {
    show: (message) => ntLoading.show(message),
    hide: () => ntLoading.hide(),
    showIn: (id, message) => ntLoading.showIn(id, message),
    hideIn: (id) => ntLoading.hideIn(id),
};
```

Kemudian pakai di Blade:

```html
<button live-click="save" live-callback-after="showAlert.success">
    Simpan
</button>
```

---

## 📝 Contoh Penggunaan di Controller

```php
// Di controller
public function store(Request $request)
{
    // Validasi & save

    return redirect()->back()->with('success', 'Data berhasil disimpan');
}
```

Alert otomatis muncul di layout karena sudah menggunakan `<x-alert>` component untuk session flash messages.

---

## 🎨 Styling

Semua alert dan loading sudah menggunakan Netra UI Design System:

- **Loading**: `<x-loading.spinner>` dengan 6 gaya (ring/dual/arc/dots/bars/ripple)
- **Alert**: `<x-alert>` dengan 7 tone (primary/accent/success/error/warning/info/neutral) dan 4 mode (soft/solid/outline/glass)

---

## ⚡ Tips

1. **Loading message**: Gunakan `data-loading-message` untuk pesan yang lebih deskriptif
2. **Alert position**: Default toast position adalah `top-right`, bisa diubah via `position` prop
3. **Auto-dismiss**: Toast otomatis hilang setelah 4.2 detik, gunakan `duration: 0` untuk sticky
4. **Callback chain**: Bisa chain multiple callbacks dengan LiveDOM

---

## 📚 Referensi

- Netra UI Loading: `resources/views/components/loading/`
- Netra UI Alert: `resources/views/components/alert/`
- JS API: `resources/js/netra-loading.js` & `resources/js/netra-alerts.js`
