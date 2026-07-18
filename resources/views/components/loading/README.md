# Netra UI — Loading & Progress (Blade Component)

Komponen di `resources/views/components/loading/`:

| File | Dipanggil sebagai | Peran |
|---|---|---|
| `spinner.blade.php` | `<x-loading.spinner>` | 6 gaya spinner (ring/dual/arc/dots/bars/ripple), 4 ukuran. |
| `progress.blade.php` | `<x-loading.progress>` | Progress bar linear — determinate & indeterminate. |
| `progress-circle.blade.php` | `<x-loading.progress-circle>` | Progress lingkaran gradient indigo→cyan. |
| `skeleton.blade.php` | `<x-loading.skeleton>` | Satu blok skeleton (text/title/avatar/thumb/badge/btn). |
| `skeleton-card.blade.php` | `<x-loading.skeleton-card>` | Wrapper kartu untuk komposisi beberapa `<x-loading.skeleton>`. |
| `overlay.blade.php` | `<x-loading.overlay>` | Overlay loading full-page atau di dalam card (render statis). |

**Button loading state** tidak perlu Blade component terpisah — sudah didukung langsung oleh `<x-button loading loading-text="…">` (lihat `components/button/index.blade.php`), atau lewat JS: `ntLoading.button(btnEl, true/false)`.

## Instalasi

Pastikan `netra-loading.css` dan `netra-loading.js` sudah ter-load.

## Contoh Pakai

```blade
<x-loading.spinner style="dual" size="lg" />
<span class="flex items-center gap-3 px-4 py-2 rounded-lg" style="background:var(--nt-alert-success-solid)">
    <x-loading.spinner style="ring" size="sm" white /> <span class="text-white text-[12px]">On color</span>
</span>

<x-loading.progress :value="68" tone="accent" label />
<x-loading.progress indeterminate />
<x-loading.progress-circle :value="72" />

<x-loading.skeleton-card>
    <div class="nt-skeleton-row mb-4">
        <x-loading.skeleton variant="avatar" />
        <div class="flex-1">
            <x-loading.skeleton width="60" />
            <x-loading.skeleton width="40" />
        </div>
    </div>
    <x-loading.skeleton width="100" />
    <x-loading.skeleton width="90" />
</x-loading.skeleton-card>

{{-- Overlay statis di dalam card ber-position:relative --}}
<div class="relative" style="min-height:160px">
    ...konten card...
    <x-loading.overlay text="Memuat data…" contained />
</div>
```

### Overlay via JS (cara paling umum, tanpa markup Blade)

```js
ntLoading.show('Menyimpan perubahan…');   // full-page
ntLoading.hide();
ntLoading.showIn('nt-card-overlay-demo', 'Memuat data…');
ntLoading.hideIn('nt-card-overlay-demo');
```

## Props Ringkas

**`<x-loading.spinner>`**: `style` (ring/dual/arc/dots/bars/ripple), `size` (sm/md/lg/xl), `white` (bool).

**`<x-loading.progress>`**: `value` (int), `tone` (primary/accent/success/error/warning), `size` (sm/md/lg), `indeterminate` (bool), `label` (bool).

**`<x-loading.skeleton>`**: `variant` (text/title/avatar/avatar-sm/thumb/badge/btn), `width` (khusus text, 0–100).

**`<x-loading.overlay>`**: `text`, `contained` (bool), `open` (bool), `spinner` (nama gaya spinner).
