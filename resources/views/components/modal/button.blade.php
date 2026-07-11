{{--
    <x-modal.button> — Trigger untuk membuka <x-modal.shell>.
    Dibangun DI ATAS <x-button>, jadi semua props styling <x-button>
    (variant, size, icon, iconTrailing, iconOnly, pill, loading,
    loadingText, badge, disabled, dll) berlaku sama persis di sini.
    Lihat dokumentasi lengkap di components/button.blade.php.

    Contoh:
        <x-modal.button target="modal-sm">Tambah</x-modal.button>

        <x-modal.button target="modal-confirm-delete" variant="danger"
            icon="fa-solid fa-trash">
            Hapus
        </x-modal.button>

        <x-modal.button target="modal-lg" modal-size="lg" variant="outline">
            Detail
        </x-modal.button>

        <x-modal.button target="modal-sm" icon-only icon="fa-solid fa-pencil"
            title="Edit" variant="ghost" />

    Props tambahan (di luar props <x-button>):
        target      WAJIB — harus sama dengan id di <x-modal.shell>.
        modal-size  xs|sm|md|lg|xl|full — override ukuran MODAL saat
                    dibuka (BUKAN ukuran tombol — itu pakai `size`
                    bawaan <x-button>, contoh: size="sm").
        nested      bool — paksa tandai sbg modal nested (biasanya
                    tidak perlu, auto-detect).

    Sama seperti sebelumnya: komponen ini HANYA menyimpan "alamat"
    modal yang harus dibuka — sama sekali tidak menyentuh AJAX.
    AJAX sepenuhnya tanggung jawabmu sendiri, lewat event
    `nt-modal:show` yang ditembakkan ke elemen shell SEBELUM modal
    ditampilkan. Lihat README-modal-component.md bagian "Ajax milik
    sendiri".
--}}
@props([
    'target' => null,
    'nested' => false,
])

@php
    if (empty($target)) {
        throw new \InvalidArgumentException(
            '<x-modal.button> membutuhkan attribute [target] yang menunjuk ke id <x-modal.shell>.',
        );
    }
@endphp

<x-button data-nt-modal-btn data-nt-modal-target="{{ $target }}" data-nt-modal-nested="{{ $nested ? '1' : '0' }}"
    {{ $attributes }}>
    {{ $slot }}
</x-button>
