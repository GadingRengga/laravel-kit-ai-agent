@props([
    'align' => 'end', // end | start | center
])

@php
    $justify = match ($align) {
        'start'  => 'justify-start',
        'center' => 'justify-center',
        default  => 'justify-end',
    };
@endphp

<div {{ $attributes->class(['nt-cell-actions', $justify]) }}>
    {{ $slot }}
</div>
