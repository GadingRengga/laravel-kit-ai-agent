{{--
    <x-button.dropdown-header> — Header info di bagian atas dropdown
    (biasa dipakai untuk nama user + email di dropdown akun).

    Contoh:
        <x-button.dropdown-header title="Gading Dev" sub="gading@netra-ui.io" />
--}}
@props([
    'title' => null,
    'sub' => null,
])

<div {{ $attributes->class(['nt-dd-header']) }}>
    @if($title)
        <div class="nt-dd-header-title">{{ $title }}</div>
    @endif
    @if($sub)
        <div class="nt-dd-header-sub">{{ $sub }}</div>
    @endif
    {{ $slot ?? '' }}
</div>
