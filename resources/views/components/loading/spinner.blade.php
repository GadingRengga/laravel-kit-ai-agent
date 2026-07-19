{{--
    <x-loading.spinner> — Spinner Netra UI, 6 gaya, 4 ukuran.

    Contoh:
        <x-loading.spinner />
        <x-loading.spinner style="dual" size="lg" />
        <x-loading.spinner style="dots" size="sm" />
        <x-loading.spinner style="ring" size="sm" white />  

    Props:
        style   ring | dual | arc | dots | bars | ripple      (default: dual)
        size    sm | md | lg | xl                             (default: md)
        white   bool — varian putih untuk dipakai di atas background solid berwarna
--}}
@props([
    'style' => 'dual',
    'size' => 'md',
    'white' => false,
])

<span {{ $attributes->class(["nt-spinner-{$style}", "nt-spin-{$size}", 'nt-spin-white' => $white]) }}>
    @if ($style === 'arc')
        <svg viewBox="0 0 40 40">
            <circle cx="20" cy="20" r="16"></circle>
        </svg>
    @elseif($style === 'dots')
        <span></span><span></span><span></span>
    @elseif($style === 'bars')
        <span></span><span></span><span></span><span></span>
    @elseif($style === 'ripple')
        <span></span><span></span>
    @endif
</span>
