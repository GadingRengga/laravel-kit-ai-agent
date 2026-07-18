{{--
    <x-tabs> — Shell navigasi tab Netra UI. 4 gaya visual, 4 mode URL — satu engine JS (netra-tabs.js).

    Contoh (underline, client-side, tanpa URL):
        <x-tabs variant="underline" mode="client" group="demo-underline">
            <x-slot:tabs>
                <x-tabs.tab value="overview" active>Overview</x-tabs.tab>
                <x-tabs.tab value="activity" badge="12">Activity</x-tabs.tab>
                <x-tabs.tab value="team">Team</x-tabs.tab>
            </x-slot:tabs>

            <x-tabs.panel value="overview">Ringkasan performa project minggu ini.</x-tabs.panel>
            <x-tabs.panel value="activity">12 aktivitas baru dari tim kamu.</x-tabs.panel>
            <x-tabs.panel value="team">Daftar anggota tim.</x-tabs.panel>
        </x-tabs>

    Contoh (pill, mode hash — URL jadi #demo-pill=...):
        <x-tabs variant="pill" mode="hash" group="demo-pill"> ... </x-tabs>

    Contoh (vertical rail, mode query — URL jadi ?tab=...):
        <x-tabs variant="vertical" mode="query" group="demo-vertical" orientation="vertical" param="tab">
            ...
        </x-tabs>

    Contoh (folder, mode href — tab = link asli ke halaman lain):
        <x-tabs variant="folder" mode="href">
            <x-slot:tabs>
                <x-tabs.tab href="/dashboard" value="dashboard" icon="fa-solid fa-house">Dashboard</x-tabs.tab>
                <x-tabs.tab href="/forms" value="forms" icon="fa-solid fa-swatchbook">Forms</x-tabs.tab>
            </x-slot:tabs>
            <x-tabs.panel value="forms">Konten halaman ini.</x-tabs.panel>
        </x-tabs>

    Props:
        variant       folder | underline | pill | vertical         (default: underline)
        mode          href | hash | query | client                 (default: client)
        group         string|null — dibutuhkan untuk mode hash/query supaya #hash / ?query unik per instance
        orientation   horizontal | vertical                        (default: horizontal, otomatis vertical kalau variant="vertical")
        param         string|null — nama query string custom untuk mode query (default: "tab")

    Slots:
        tabs      — daftar <x-tabs.tab>
        default   — daftar <x-tabs.panel>

    JS: butuh netra-tabs.js. Inisialisasi otomatis lewat `data-nt-tabs`; untuk konten yang dirender ulang
    via AJAX/Livewire, panggil ulang `window.NetraUI.initTabs(scopeEl)`.
--}}
@props([
    'variant' => 'underline',
    'mode' => 'client',
    'group' => null,
    'orientation' => null,
    'param' => null,
])

@php
    $variantClass = "nt-tabs--{$variant}";
    $orientation = $orientation ?? ($variant === 'vertical' ? 'vertical' : null);
@endphp

<div {{ $attributes->class(['nt-tabs', $variantClass]) }}
    data-nt-tabs
    data-mode="{{ $mode }}"
    @if($group) data-group="{{ $group }}" @endif
    @if($orientation) data-orientation="{{ $orientation }}" @endif
    @if($param) data-param="{{ $param }}" @endif
>
    <div class="nt-tabs-list">
        {{ $tabs }}
        <span class="nt-tabs-indicator"></span>
    </div>
    <div class="nt-tabs-panels">
        {{ $slot }}
    </div>
</div>
