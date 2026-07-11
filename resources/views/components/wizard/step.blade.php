@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'optional' => false,
    'url' => null,
])

@php
    if (empty($title)) {
        throw new \InvalidArgumentException('<x-wizard.step> membutuhkan attribute [title].');
    }
@endphp

<section class="nt-wizard-step" data-step-title="{{ $title }}"
    @if ($subtitle) data-step-subtitle="{{ $subtitle }}" @endif
    @if ($icon) data-step-icon="{{ $icon }}" @endif
    data-step-optional="{{ $optional ? 'true' : 'false' }}"
    @if ($url) data-step-url="{{ $url }}"
        data-step-loaded="false" @endif>
    <div data-step-mount>
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @elseif($url)
            {{-- Placeholder selama menunggu response AJAX pertama kali.
                 Ditimpa otomatis oleh resources/js/netra-form-wizard-lazy.js --}}
            <div class="nt-wizard-step-skeleton" data-step-skeleton aria-hidden="true">
                <div class="nt-wizard-step-skeleton-line" style="width:40%"></div>
                <div class="nt-wizard-step-skeleton-line" style="width:88%"></div>
                <div class="nt-wizard-step-skeleton-line" style="width:70%"></div>
                <div class="nt-wizard-step-skeleton-line" style="width:55%"></div>
            </div>
        @endif
    </div>
</section>
