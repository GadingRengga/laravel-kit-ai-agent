@props([
    'name',
    'value',
    'title',
    'description' => null,
    'badge' => null,
    'checked' => false,
])

@php
    $id = $attributes->get('id', $name . '_' . Str::slug($value));
    $old = old($name);
    $isChecked = $old !== null ? ((string) $old === (string) $value) : $checked;
    $activeClass = $isChecked
        ? 'border-2 border-brand-500 bg-brand-50 dark:bg-brand-900/20'
        : 'border border-surface-200 dark:border-slate-600 hover:border-surface-300 dark:hover:border-slate-500';
@endphp

<label
    class="radio-card flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all {{ $activeClass }}"
    for="{{ $id }}"
    data-radio-card-group="{{ $name }}"
>
    <input
        type="radio"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        @checked($isChecked)
        {{ $attributes->except(['value', 'id', 'class'])->merge(['class' => 'form-radio']) }}
    />
    <div class="flex-1 min-w-0">
        <p class="text-[13px] font-semibold text-surface-900 dark:text-white">{{ $title }}</p>
        @if($description)
            <p class="text-[11.5px] text-surface-400 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @if($badge)
        <span class="text-[10px] bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 px-1.5 py-0.5 rounded-full font-semibold shrink-0">
            {{ $badge }}
        </span>
    @endif
</label>

@once
    <script>
        document.addEventListener('change', function (e) {
            const input = e.target;
            if (!input.matches('.radio-card input[type="radio"]')) return;
            const group = input.closest('[data-radio-card-group]')?.getAttribute('data-radio-card-group');
            if (!group) return;
            document.querySelectorAll(`.radio-card[data-radio-card-group="${group}"]`).forEach(function (card) {
                const active = card.querySelector('input').checked;
                card.classList.toggle('border-2', active);
                card.classList.toggle('border-brand-500', active);
                card.classList.toggle('bg-brand-50', active);
                card.classList.toggle('dark:bg-brand-900/20', active);
                card.classList.toggle('border', !active);
                card.classList.toggle('border-surface-200', !active);
                card.classList.toggle('dark:border-slate-600', !active);
            });
        });
    </script>
@endonce
