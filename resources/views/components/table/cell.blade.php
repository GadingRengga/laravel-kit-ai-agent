@props([
    'label' => null,
    'sortValue' => null,
    'align' => 'left',
])

@php
    $alignClass = match ($align) {
        'right' => 'nt-text-right',
        'center' => 'nt-text-center',
        default => '',
    };
@endphp

<td {{ $attributes->class([$alignClass]) }} @if ($label) data-label="{{ $label }}" @endif
    @if ($sortValue !== null) data-sort-value="{{ $sortValue }}" @endif>
    {{ $slot }}
</td>
