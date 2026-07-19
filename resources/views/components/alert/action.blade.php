{{--
    <x-alert.action> — Tombol aksi kecil di dalam <x-slot:actions> milik <x-alert>.

    Contoh:
        <x-alert tone="warning" title="3 item dipindahkan ke sampah">
            Item akan dihapus permanen setelah 30 hari.
            <x-slot:actions>
                <x-alert.action onclick="ntAlert.dismiss(this)">Urungkan</x-alert.action>
                <x-alert.action ghost onclick="ntAlert.dismiss(this)">Abaikan</x-alert.action>
            </x-slot:actions>
        </x-alert>

    Props:
        ghost   bool — versi transparan/sekunder
--}}
@props([
    'ghost' => false,
])

<button type="button"
    {{ $attributes->class(['nt-alert-action-btn', 'is-ghost' => $ghost]) }}>{{ $slot }}</button>
