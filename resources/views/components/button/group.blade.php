{{--
    <x-button.group> — Bungkus beberapa <x-button> jadi satu grup berdempetan.
    Cocok untuk segmented control, toolbar toggle, view switcher, dll.

    Contoh:
        <x-button.group>
            <x-button variant="primary">Grid</x-button>
            <x-button variant="secondary">List</x-button>
            <x-button variant="secondary">Table</x-button>
        </x-button.group>

        <x-button.group size="sm">
            <x-button variant="secondary" size="sm">Hari ini</x-button>
            <x-button variant="primary" size="sm">7 Hari</x-button>
        </x-button.group>

    Props:
        size    sm | md   (default: md) — samakan dengan size tombol di dalamnya
--}}
@props([
    'size' => 'md',
])

<div {{ $attributes->class(['nt-btn-group', 'nt-btn-group-sm' => $size === 'sm']) }}>
    {{ $slot }}
</div>
