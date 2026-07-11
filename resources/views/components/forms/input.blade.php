@props([
    'name',
    'label' => null,
    'type' => 'text',
    'hint' => null,
    'required' => false,
    'prefix' => null,      // teks di kiri, contoh: "https://"
    'suffix' => null,      // teks di kanan, contoh: "IDR"
    'icon' => null,        // fa icon class di kiri, contoh: "fa-solid fa-magnifying-glass"
    'toggleable' => false, // khusus type="password", tampilkan tombol show/hide
    'success' => false,    // true / string pesan sukses
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $hasAddon = $prefix || $suffix || $icon || $toggleable;
    $value = $type === 'password' ? null : old($name, $attributes->get('value'));
    $state = $error ? 'is-error' : ($success ? 'is-success' : '');
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }} @if($required)<span class="required">*</span>@endif
        </label>
    @endif

    @if($hasAddon)
        <div class="input-group">
            @if($icon)
                <span class="input-addon"><i class="{{ $icon }} text-[12px]"></i></span>
            @elseif($prefix)
                <span class="input-addon">{{ $prefix }}</span>
            @endif

            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $id }}"
                @if($value !== null) value="{{ $value }}" @endif
                {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => "form-input {$state}"]) }}
            />

            @if($toggleable && $type === 'password')
                <button type="button" class="input-addon-btn" data-toggle-password="{{ $id }}" title="Show/hide">
                    <i class="fa-regular fa-eye text-[13px]"></i>
                </button>
            @elseif($suffix)
                <span class="input-addon">{{ $suffix }}</span>
            @endif
        </div>
    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            @if($value !== null) value="{{ $value }}" @endif
            {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => "form-input {$state}"]) }}
        />
    @endif

    @if($error)
        <span class="form-msg-error"><i class="fa-solid fa-circle-exclamation text-[11px]"></i>{{ $error }}</span>
    @elseif(is_string($success) && $success)
        <span class="form-msg-success"><i class="fa-solid fa-circle-check text-[11px]"></i>{{ $success }}</span>
    @elseif($hint)
        <span class="form-hint">{{ $hint }}</span>
    @endif
</div>

@once
    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-toggle-password]');
            if (!btn) return;
            const input = document.getElementById(btn.getAttribute('data-toggle-password'));
            if (!input) return;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash text-[13px]';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye text-[13px]';
            }
        });
    </script>
@endonce
