@props([
    'title' => null,
    'subtitle' => null,
    'close' => true,
])

{{-- id sengaja TIDAK di-props lagi. Title-id & wiring aria
     di-resolve otomatis oleh JS berdasarkan id backdrop tempat
     content ini akhirnya ditanam/disuntik. --}}

<div {{ $attributes }}>
    <div class="nt-modal-header" data-nt-modal-header>
        <div class="nt-modal-title-wrap">
            <p class="nt-modal-title" data-nt-modal-title>{{ $title }}</p>
            @if ($subtitle)
                <p class="nt-modal-subtitle" data-nt-modal-subtitle>{{ $subtitle }}</p>
            @endif
        </div>
        @if ($close)
            <button type="button" class="nt-modal-close" data-nt-modal-close aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        @endif
    </div>

    <div class="nt-modal-body" data-nt-modal-body>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="nt-modal-footer" data-nt-modal-footer>
            {{ $footer }}
        </div>
    @endisset
</div>
