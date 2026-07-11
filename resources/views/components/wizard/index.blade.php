@props([
    // ── perilaku navigasi (langsung dipetakan ke data-* nt-wizard) ──
    'style' => 'horizontal', // horizontal | vertical | minimal — lihat catatan di README kenapa 1 komponen, bukan 3
    'validate' => true,
    'allowStepClick' => false,
    'allowSkip' => false,
    'persist' => false,
    'persistKey' => null,
    'scrollTop' => true,
    'showStepCount' => true,
    'labelPrev' => 'Sebelumnya',
    'labelNext' => 'Selanjutnya',
    'labelSubmit' => 'Simpan',

    // ── submission ──
    // Isi 'action' kalau wizard ini memang mau submit native ke server
    // (form biasa, full reload / redirect setelah simpan).
    // Kosongkan kalau submission mau ditangani via JS/AJAX sendiri —
    // dengar event `nt-wizard:submit` (lihat README).
    'action' => null,
    'method' => 'POST',
])

@php
    $isForm = filled($action);
    $needsSpoof = $isForm && !in_array(strtoupper($method), ['GET', 'POST']);
    $tag = $isForm ? 'form' : 'div';

    $dataAttrs = [
        'data-style' => $style,
        'data-validate' => $validate ? 'true' : 'false',
        'data-allow-step-click' => $allowStepClick ? 'true' : 'false',
        'data-allow-skip' => $allowSkip ? 'true' : 'false',
        'data-persist' => $persist ? 'true' : 'false',
        'data-scroll-top' => $scrollTop ? 'true' : 'false',
        'data-show-step-count' => $showStepCount ? 'true' : 'false',
        'data-labels-prev' => $labelPrev,
        'data-labels-next' => $labelNext,
        'data-labels-submit' => $labelSubmit,
    ];
    if ($persistKey) {
        $dataAttrs['data-persist-key'] = $persistKey;
    }
@endphp

{{--
    Root wizard. Mesin navigasinya (switch antar step tanpa reload,
    validasi per-step, progress, dsb) sepenuhnya dari
    assets/js/netra-form-wizard.js bawaan tema — komponen ini cuma
    menuliskan markup + data-* attribute-nya dengan DX yang lebih enak,
    tidak menduplikasi/menimpa logic tema.

    Kenapa "style" (horizontal/vertical/minimal) satu prop, bukan 3
    komponen terpisah <x-wizard-horizontal>, <x-wizard-vertical>, dst?
    → Karena markup step (<x-wizard.step>) IDENTIK untuk ketiganya;
      yang beda cuma cara header/progress dirender, dan itu sudah
      ditangani otomatis oleh netra-form-wizard.js lewat satu
      attribute data-style. Memisah jadi 3 komponen cuma menduplikasi
      root wrapper tanpa manfaat, dan bikin API-nya lebih besar tanpa
      alasan (developer harus hafal 3 nama komponen utk 1 hal yang
      sama). Jadi cukup: <x-wizard style="vertical">.
--}}
<{{ $tag }}
    @if ($isForm) action="{{ $action }}"
        method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" @endif
    {{ $attributes->merge(array_merge(['class' => 'nt-wizard'], $dataAttrs)) }}>
    @if ($isForm)
        @csrf
        @if ($needsSpoof)
            @method($method)
        @endif
    @endif

    {{-- Taruh <x-wizard.step> di sini. Head & foot (Prev/Next/Simpan)
         akan dibuat otomatis oleh JS kalau tidak diisi manual. --}}
    {{ $slot }}

    @isset($footer)
        {{-- Footer custom — kalau diisi, JS TIDAK menimpa dengan
             footer otomatis (tandai data-custom, dibaca oleh
             netra-form-wizard.js). Gunakan attribute
             data-wizard-prev / data-wizard-next / data-wizard-submit
             pada tombolmu supaya tetap terhubung ke mesin navigasi. --}}
        <div class="nt-wizard-foot" data-custom="true">
            {{ $footer }}
        </div>
    @endisset
    </{{ $tag }}>
