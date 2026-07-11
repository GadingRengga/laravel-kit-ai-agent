@props([
    'id'             => 'editable-table',
    'currencyPrefix' => 'Rp ',
    'nameField'      => 'name',
    'entityLabel'    => 'Item',
    'statusField'    => null,   // field yg dipakai utk hitung status badge otomatis, mis. "stock"
    'statusRules'    => [],     // array PHP biasa, contoh lihat example-usage.blade.php
])

<table
    {{ $attributes->class('nt-table') }}
    id="{{ $id }}"
    data-nt-editable-table
    data-currency-prefix="{{ $currencyPrefix }}"
    data-name-field="{{ $nameField }}"
    data-entity-label="{{ $entityLabel }}"
    @if ($statusField) data-status-field="{{ $statusField }}" @endif
    @if (!empty($statusRules)) data-status-rules='{{ json_encode($statusRules) }}' @endif
>
    <thead>
        <tr>{{ $head }}</tr>
    </thead>
    <tbody id="{{ $id }}-tbody" data-nt-editable-tbody>
        {{ $slot }}
    </tbody>
</table>
