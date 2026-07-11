@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
    'rows' => 4,
    'counter' => false, // true untuk aktifkan character counter, wajib pakai bareng maxlength
    'success' => false,
])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $state = $error ? 'is-error' : ($success ? 'is-success' : '');
    $value = old($name, $attributes->get('value', $slot ?? ''));
    $maxlength = $attributes->get('maxlength');
@endphp

<div class="form-group @if($counter) md:col-span-2 @endif">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }} @if($required)<span class="required">*</span>@endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        @if($counter) oninput="this.closest('.form-group').querySelector('[data-char-count]').textContent = this.value.length" @endif
        {{ $attributes->except(['value', 'id', 'class', 'rows'])->merge(['class' => "form-textarea {$state}"]) }}
    >{{ $value }}</textarea>

    <div class="flex items-center justify-between mt-1">
        <span>
            @if($error)
                <span class="form-msg-error"><i class="fa-solid fa-circle-exclamation text-[11px]"></i>{{ $error }}</span>
            @elseif(is_string($success) && $success)
                <span class="form-msg-success"><i class="fa-solid fa-circle-check text-[11px]"></i>{{ $success }}</span>
            @elseif($hint)
                <span class="form-hint">{{ $hint }}</span>
            @endif
        </span>
        @if($counter && $maxlength)
            <span class="text-[11.5px] text-surface-400 dark:text-slate-500 font-mono">
                <span data-char-count>{{ strlen($value) }}</span>/{{ $maxlength }}
            </span>
        @endif
    </div>
</div>
