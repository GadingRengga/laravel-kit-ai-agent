@props([
    'name',
    'label' => null,
    'description' => null, // teks kecil di bawah label, membuat style "permission item"
    'value' => '1',
    'checked' => false,
])

@php
    $id = $attributes->get('id', $name . '_' . Str::slug($value));
    $old = old($name);
    $isChecked = $checked || ($old !== null && (is_array($old) ? in_array($value, $old) : (string) $old === (string) $value));
@endphp

<label class="form-checkbox-wrap" for="{{ $id }}">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        @checked($isChecked)
        {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => 'form-checkbox']) }}
    />
    @if($description)
        <div>
            <p class="form-checkbox-label font-medium">{{ $label }}</p>
            <p class="text-[11.5px] text-surface-400 dark:text-slate-500">{{ $description }}</p>
        </div>
    @else
        <span class="form-checkbox-label">{{ $label ?? $slot }}</span>
    @endif
</label>
