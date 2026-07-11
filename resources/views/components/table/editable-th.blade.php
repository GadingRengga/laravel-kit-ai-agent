@props(['editable' => false])

<th {{ $attributes }}>
    {{ $slot }}
    @if ($editable)
        <span class="nt-th-edit-hint">&#9998;</span>
    @endif
</th>
