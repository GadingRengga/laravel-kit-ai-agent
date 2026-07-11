{{--
    <x-button.dropdown> — Dropdown generik. Trigger-nya bebas: bisa <x-button>,
    avatar, teks apapun. Butuh netra-dropdown.js — tanpa id manual.

    Contoh (dropdown akun di topbar):
        <x-button.dropdown align="right">
            <x-slot:trigger>
                <x-button variant="secondary" icon="fa-solid fa-user-gear" icon-trailing="fa-solid fa-chevron-down">
                    Akun
                </x-button>
            </x-slot:trigger>

            <x-button.dropdown-header title="Gading Dev" sub="gading@netra-ui.io" />
            <x-button.dropdown-section>
                <x-button.dropdown-item icon="fa-regular fa-user" href="/profile">Profil Saya</x-button.dropdown-item>
                <x-button.dropdown-item icon="fa-solid fa-gear" href="/settings">Pengaturan</x-button.dropdown-item>
            </x-button.dropdown-section>
            <x-button.dropdown-divider />
            <x-button.dropdown-section>
                <x-button.dropdown-item icon="fa-solid fa-right-from-bracket" danger href="/logout">
                    Sign out
                </x-button.dropdown-item>
            </x-button.dropdown-section>
        </x-button.dropdown>

        {{-- Contoh row action di tabel (icon-only trigger, align kanan, buka ke atas) --}}
        <x-button.dropdown align="right" :up="true">
            <x-slot:trigger>
                <x-button variant="ghost" size="sm" icon-only icon="fa-solid fa-ellipsis-vertical" title="Menu" />
            </x-slot:trigger>
            <x-button.dropdown-section>
                <x-button.dropdown-item icon="fa-solid fa-pencil">Edit</x-button.dropdown-item>
                <x-button.dropdown-item icon="fa-solid fa-trash-can" danger>Hapus</x-button.dropdown-item>
            </x-button.dropdown-section>
        </x-button.dropdown>

    Props:
        align   left | right     (default: left)
        up      bool — buka menu ke atas (default: false)

    Slot:
        trigger   (named)  — elemen pemicu dropdown
        default            — isi menu, susun bebas pakai:
                              <x-button.dropdown-header>, <x-button.dropdown-section>,
                              <x-button.dropdown-item>, <x-button.dropdown-divider>
--}}
@props([
    'align' => 'left',
    'up' => false,
])

<div {{ $attributes->class(['nt-dropdown']) }} data-nt-dropdown>
    <div data-nt-dropdown-toggle style="display:inline-flex" aria-haspopup="true" aria-expanded="false">
        {{ $trigger }}
    </div>

    <div @class([
        'nt-dropdown-menu',
        'align-right' => $align === 'right',
        'drop-up' => $up,
    ]) data-nt-dropdown-menu>
        {{ $slot }}
    </div>
</div>
