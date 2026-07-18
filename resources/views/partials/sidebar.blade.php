@php
    use App\Models\Superuser\Menu;

    $buildNavItems = function ($menus, $prefix = '', $level = 0) use (&$buildNavItems) {
        return $menus
            ->map(function (Menu $menu) use ($prefix, $level, $buildNavItems) {
                $currentPrefix = $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;';
                $item = [
                    'label' => $menu->name,
                    'icon' => $menu->icon ?: 'fa-solid fa-circle-dot',
                    'route' => $menu->route ?: '#',
                    'prefix' => $prefix,
                    'level' => $level,
                ];

                if ($menu->children->isNotEmpty()) {
                    $item['children'] = $buildNavItems($menu->children, $currentPrefix, $level + 1);
                }

                return $item;
            })
            ->all();
    };

    $navGroups = $navGroups ?? [
        [
            'label' => null,
            'items' => $buildNavItems(
                Menu::query()
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->with('allChildren')
                    ->orderBy('order')
                    ->get(),
            ),
        ],
    ];
@endphp

<aside id="sidebar" class="flex flex-col">

    {{-- Logo --}}
    <div class="sidebar-logo flex items-center gap-3 px-4 h-16 shrink-0">
        <div class="sidebar-logo-mark w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
            <i class="fa-solid fa-eye text-white text-[13px]"></i>
        </div>
        <div
            class="sidebar-brand-text flex flex-col leading-tight overflow-hidden whitespace-nowrap transition-all duration-200">
            <span class="font-bold text-[15px] tracking-tight user-name">{{ config('app.name', 'Netra UI') }}<span
                    class="brand-dot"></span></span>
            <span class="text-[10.5px] font-normal user-role">Enterprise Suite</span>
        </div>
        <button onclick="closeMobileSidebar()" class="close-btn ml-auto lg:hidden p-1 rounded">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-0.5">
        @forelse ($navGroups as $group)
            @if (!empty($group['label']))
                <div
                    class="nav-section-label nav-section-title px-2 pt-4 pb-1.5 text-[10px] font-semibold uppercase tracking-widest whitespace-nowrap transition-all duration-200 first:pt-1">
                    {{ $group['label'] }}
                </div>
            @endif

            @foreach ($group['items'] as $item)
                @include('partials.sidebar-item', ['item' => $item, 'level' => $item['level'] ?? 0])
            @endforeach
        @empty
            {{-- Belum ada menu sama sekali di database --}}
            <div class="px-3 py-4 text-[12px] text-slate-400 leading-relaxed">
                Belum ada menu. Tambahkan lewat
                <a href="{{ url('management/menu') }}" class="text-indigo-500 hover:underline">Manajemen Menu</a>.
            </div>
        @endforelse
    </nav>

    {{-- Footer sidebar --}}
    <div class="sidebar-footer shrink-0 p-3 space-y-0.5">
        <button onclick="toggleSidebar()"
            class="collapse-btn hidden lg:flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-[13px] font-medium transition-colors">
            <i id="collapse-icon"
                class="fa-solid fa-angles-left text-[14px] shrink-0 w-5 text-center transition-transform duration-250"></i>
            <span class="nav-label whitespace-nowrap transition-all duration-200">Collapse</span>
        </button>

        <div class="user-row flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-colors">
            <div
                class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[11px] font-bold shrink-0">
                {{ Str::of(auth()->user()->name ?? 'Guest')->explode(' ')->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('') }}
            </div>
            <div class="sidebar-user-info overflow-hidden min-w-0">
                <p class="user-name text-[13px] font-medium whitespace-nowrap leading-tight">
                    {{ auth()->user()->name ?? 'Guest' }}</p>
                <p class="user-role text-[11px] whitespace-nowrap leading-tight">
                    {{ auth()->user()->role ?? 'Administrator' }}</p>
            </div>
            <i
                class="user-dots fa-solid fa-ellipsis-vertical text-xs ml-auto nav-label transition-all duration-200"></i>
        </div>
    </div>
</aside>
