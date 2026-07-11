@props([
    'id' => 'collapse-table',
])

<div class="nt-table-wrap nt-table-wrap-flush">
    <table {{ $attributes->class('nt-table') }} id="{{ $id }}" data-nt-collapse-table>
        <thead>
            <tr>{{ $head }}</tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
