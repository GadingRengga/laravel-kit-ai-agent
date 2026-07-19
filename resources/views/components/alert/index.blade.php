{{--
    <x-alert> — Inline / banner alert Netra UI.

    Contoh:
        <x-alert tone="success" title="Pembayaran berhasil" icon="fa-solid fa-circle-check">
            Invoice #INV-2026-0417 telah lunas dan diproses.
        </x-alert>

        <x-alert tone="warning" mode="solid" title="Sesi akan berakhir">
            Kamu akan logout otomatis dalam 2 menit karena idle.
        </x-alert>

        <x-alert tone="error" title="Gagal memproses transaksi" dismissible>
            Saldo kartu tidak mencukupi.
            <x-slot:actions>
                <button type="button" class="nt-alert-action-btn">Coba Lagi</button>
            </x-slot:actions>
        </x-alert>

        <x-alert tone="primary" compact title="Nomor telepon terverifikasi" />

        <x-alert tone="primary" banner icon="fa-solid fa-gift" title="Promo tahun baru — diskon 30%.">
            Berlaku sampai akhir bulan ini.
        </x-alert>

       
        <div style="background:linear-gradient(120deg,#4F46E5,#06B6D4 60%,#22C55E)" class="p-6 rounded-2xl">
            <x-alert tone="success" mode="glass" title="Backup selesai">3.2GB berhasil dicadangkan.</x-alert>
        </div>

    Props:
        tone          primary | accent | success | error | warning | info | neutral   (default: primary)
        mode          soft | solid | outline | glass                                  (default: soft)
        title         string — judul alert (opsional kalau mau isi manual via slot)
        icon          class FontAwesome, default otomatis mengikuti tone
        dismissible   bool — tampilkan tombol close (x), default true
        compact       bool — versi ringkas satu baris, tanpa desc
        banner        bool — versi full-width untuk pengumuman

    Slots:
        default   — isi <p class="nt-alert-desc">…</p> (dipakai sebagai deskripsi kalau title diisi,
                    atau sebagai konten bebas kalau title kosong)
        actions   — tombol aksi (nt-alert-action-btn) di bawah deskripsi

    Catatan: butuh netra-alerts.css + netra-alerts.js (juga dipakai untuk toast via `ntAlert.toast()`).
    Dismiss manual: onclick="ntAlert.dismiss(this)" pada tombol close (sudah otomatis kalau dismissible).
--}}
@props([
    'tone' => 'primary',
    'mode' => 'soft',
    'title' => null,
    'icon' => null,
    'dismissible' => true,
    'compact' => false,
    'banner' => false,
])

@php
    $defaultIcons = [
        'primary' => 'fa-solid fa-bell',
        'accent' => 'fa-solid fa-sparkles',
        'success' => 'fa-solid fa-circle-check',
        'error' => 'fa-solid fa-circle-exclamation',
        'warning' => 'fa-solid fa-triangle-exclamation',
        'info' => 'fa-solid fa-circle-info',
        'neutral' => 'fa-solid fa-comment-dots',
    ];
    $iconClass = $icon ?? ($defaultIcons[$tone] ?? 'fa-solid fa-bell');
@endphp

<div {{ $attributes->class([
    'nt-alert',
    "nt-alert-{$mode}",
    "nt-alert-tone-{$tone}",
    'nt-alert-compact' => $compact,
    'nt-alert-banner' => $banner,
]) }}
    role="alert">
    <div class="nt-alert-icon"><i class="{{ $iconClass }}"></i></div>

    <div class="nt-alert-content">
        @if ($title)
            <p class="nt-alert-title">{{ $title }}</p>
            @if (!$compact && ($slot->isNotEmpty() || isset($actions)))
                <p class="nt-alert-desc">{{ $slot }}</p>
            @endif
        @else
            {{ $slot }}
        @endif

        @isset($actions)
            <div class="nt-alert-actions">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @if ($dismissible)
        <button type="button" class="nt-alert-close" onclick="ntAlert.dismiss(this)" aria-label="Tutup">
            <i class="fa-solid fa-xmark"></i>
        </button>
    @endif
</div>
