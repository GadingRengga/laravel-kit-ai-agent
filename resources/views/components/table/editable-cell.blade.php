@props([
    'field',
    'value',
    'type'   => 'text',   // text | number
    'format' => null,     // currency | integer — hanya berlaku jika type="number"
])

<td
    {{ $attributes->class('nt-editable') }}
    data-field="{{ $field }}"
    data-value="{{ $value }}"
    data-edit-type="{{ $type }}"
    @if ($format) data-format="{{ $format }}" @endif
    title="Klik untuk edit"
>
    {{ $slot }}
</td>
