@props([
    'id' => null,
    'size' => 'md',
    'nested' => false,
])

@php
    if (empty($id)) {
        throw new \InvalidArgumentException(
            '<x-modal.shell> membutuhkan attribute [id] yang menjadi target dari <x-modal.button>.',
        );
    }
@endphp

{{--
    CANGKANG — cuma backdrop + kotak dialog (ukuran & id-nya saja).
    Statis, ditanam SEKALI di footer layout per jenis modal.

    Tidak tahu-menahu soal header/body/footer — itu urusan
    <x-modal.content>, entah ditulis langsung di dalam slot ini
    (modal statis), atau disuntikkan belakangan lewat AJAX ke dalam
    [data-nt-modal-dialog] (modal dinamis).
--}}
@php
    $backdropId = "{$id}-backdrop";
@endphp

<div id="{{ $backdropId }}" class="nt-modal-backdrop{{ $nested ? ' nt-modal-nested' : '' }}" data-nt-modal
    data-nt-modal-base-size="{{ $size }}" onclick="ntModal.closeOnBackdrop(event, '{{ $backdropId }}')">
    <div id="{{ $id }}" class="nt-modal nt-modal-{{ $size }}" role="dialog" aria-modal="true"
        aria-labelledby="{{ $id }}-title" data-nt-modal-dialog>
        {{ $slot }}
    </div>
</div>
