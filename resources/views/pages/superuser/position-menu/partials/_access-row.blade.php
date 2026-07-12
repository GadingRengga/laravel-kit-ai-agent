@php
    $pivot = $position->menus->firstWhere('id', $menu->id)?->pivot;
    $checked = fn(string $field) => (bool) ($pivot->{$field} ?? false);
@endphp

<tr>
    <td style="padding-left: {{ 12 + ($level - 1) * 20 }}px"
        class="text-[13px] {{ $level === 1 ? 'font-medium text-slate-700 dark:text-slate-200' : 'text-slate-500 dark:text-slate-400' }}">
        @if ($level > 1)
            <i class="fa-solid fa-turn-up fa-rotate-90 text-[10px] text-slate-300 mr-1.5"></i>
        @endif
        <i class="{{ $menu->icon ?: 'fa-solid fa-circle-dot' }} mr-1.5 text-[12px] text-slate-400"></i>{{ $menu->name }}
        <input type="hidden" name="menu_ids[]" value="{{ $menu->id }}">
    </td>
    <td class="nt-text-center">
        <x-forms.checkbox name="access[{{ $menu->id }}][can_view]" :checked="$checked('can_view')" />
    </td>
    <td class="nt-text-center">
        <x-forms.checkbox name="access[{{ $menu->id }}][can_create]" :checked="$checked('can_create')" />
    </td>
    <td class="nt-text-center">
        <x-forms.checkbox name="access[{{ $menu->id }}][can_edit]" :checked="$checked('can_edit')" />
    </td>
    <td class="nt-text-center">
        <x-forms.checkbox name="access[{{ $menu->id }}][can_delete]" :checked="$checked('can_delete')" />
    </td>
</tr>

@if ($menu->children->isNotEmpty())
    @foreach ($menu->children as $child)
        @include('pages.superuser.position-menu.partials._access-row', [
            'menu' => $child,
            'level' => $level + 1,
            'position' => $position,
        ])
    @endforeach
@endif
