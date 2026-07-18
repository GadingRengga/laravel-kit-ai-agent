{{--
    <x-loading.progress-circle> — Progress lingkaran dengan gradient indigo→cyan.

    Contoh:
        <x-loading.progress-circle :value="72" />
        <x-loading.progress-circle id="pc-upload" :value="0" />   {{-- update via JS: NetraUI.loading / ntLoading --}}

    Props:
        value   int — persentase 0-100 (default: 0)

    Catatan: butuh netra-loading.js untuk animasi fill; kalau value diisi statis lewat Blade,
    komponen tetap tampil benar tanpa JS (fallback CSS custom property).
--}}
@props([
    'value' => 0,
])

<div {{ $attributes->class(['nt-progress-circle']) }} style="--nt-pc-value: {{ $value }}">
    <svg viewBox="0 0 64 64">
        <defs>
            <linearGradient id="nt-pc-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#4F46E5" /><stop offset="100%" stop-color="#06B6D4" />
            </linearGradient>
        </defs>
        <circle class="nt-pc-track" cx="32" cy="32" r="26"></circle>
        <circle class="nt-pc-fill" cx="32" cy="32" r="26"></circle>
    </svg>
    <span class="nt-pc-value">{{ $value }}%</span>
</div>
