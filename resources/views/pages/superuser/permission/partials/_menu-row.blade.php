<tr>
    <td style="padding-left: {{ 12 + ($level - 1) * 20 }}px"
        class="text-[13px] {{ $level === 1 ? 'font-medium text-slate-700 dark:text-slate-200' : 'text-slate-500 dark:text-slate-400' }}">
        @if ($level > 1)
            <i class="fa-solid fa-turn-up fa-rotate-90 text-[10px] text-slate-300 mr-1.5"></i>
        @endif
        <i
            class="{{ $menu->icon ?: 'fa-solid fa-circle-dot' }} mr-1.5 text-[12px] text-slate-400"></i>{{ $menu->name }}
    </td>
    <td class="nt-text-center">
        <x-forms.checkbox name="menu_ids[]" value="{{ $menu->id }}" :checked="in_array($menu->id, $selectedMenuIds)" />
    </td>
</tr>

@if ($menu->children->isNotEmpty())
    @foreach ($menu->children as $child)
        @include('pages.superuser.permission.partials._menu-row', [
            'menu' => $child,
            'level' => $level + 1,
            'selectedMenuIds' => $selectedMenuIds,
        ])
    @endforeach
@endif
