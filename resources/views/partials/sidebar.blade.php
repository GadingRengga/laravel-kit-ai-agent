@php
    // Definisikan menu di sini (atau pindahkan ke config/navigation.php kalau makin banyak).
    // 'route' dicek dengan request()->is(), jadi isi dengan path relatif seperti pada href.
    $navGroups = $navGroups ?? [
        [
            'label' => 'Main',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'fa-solid fa-house', 'route' => 'dashboard'],
            ],
        ],
        [
            'label' => 'UI Components',
            'items' => [
                ['label' => 'Form Components', 'icon' => 'fa-solid fa-swatchbook', 'route' => 'form-components'],
                ['label' => 'Modal Components', 'icon' => 'fa-regular fa-window-restore', 'route' => 'modal-components'],
                ['label' => 'Button & Dropdown', 'icon' => 'fa-regular fa-hand-pointer', 'route' => 'button-components'],
                [
                    'label' => 'Table Components',
                    'icon' => 'fa-solid fa-table',
                    'children' => [
                        ['label' => 'Basic Table', 'route' => 'basic-table'],
                        ['label' => 'Advance Table', 'route' => 'advance-table'],
                        ['label' => 'Premium Table', 'route' => 'premium-table'],
                        ['label' => 'Ultimate Table', 'route' => 'ultimate-table'],
                    ],
                ],
            ],
        ],
    ];
@endphp

<aside id="sidebar" class="flex flex-col">

    {{-- Logo --}}
    <div class="sidebar-logo flex items-center gap-3 px-4 h-16 shrink-0">
        <div class="sidebar-logo-mark w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
            <i class="fa-solid fa-eye text-white text-[13px]"></i>
        </div>
        <div class="sidebar-brand-text flex flex-col leading-tight overflow-hidden whitespace-nowrap transition-all duration-200">
            <span class="font-bold text-[15px] tracking-tight user-name">{{ config('app.name', 'Netra UI') }}<span class="brand-dot"></span></span>
            <span class="text-[10.5px] font-normal user-role">Enterprise Suite</span>
        </div>
        <button onclick="closeMobileSidebar()" class="close-btn ml-auto lg:hidden p-1 rounded">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-0.5">
        @foreach ($navGroups as $group)
            <div class="nav-section-label nav-section-title px-2 pt-4 pb-1.5 text-[10px] font-semibold uppercase tracking-widest whitespace-nowrap transition-all duration-200 first:pt-1">
                {{ $group['label'] }}
            </div>

            @foreach ($group['items'] as $item)
                @php
                    $hasChildren = !empty($item['children']);
                    $isActive = !$hasChildren && request()->is($item['route'] ?? '');
                @endphp

                <div class="nav-item relative {{ $isActive ? 'active' : '' }}">
                    @if ($hasChildren)
                        <a href="#" onclick="toggleSubmenu(this,event)"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13.5px] font-medium transition-colors">
                            <i class="nav-icon {{ $item['icon'] }} text-[14px] shrink-0 w-5 text-center"></i>
                            <span class="nav-label whitespace-nowrap transition-all duration-200">{{ $item['label'] }}</span>
                            <i class="chevron fa-solid fa-chevron-right text-[10px] ml-auto"></i>
                        </a>

                        <div class="submenu pl-8 mt-1 space-y-0.5">
                            @foreach ($item['children'] as $child)
                                <div class="nav-item {{ request()->is($child['route']) ? 'active' : '' }}">
                                    <a href="{{ url($child['route']) }}"
                                        class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-[13px]">
                                        <span class="nav-label">{{ $child['label'] }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <a href="{{ url($item['route']) }}"
                            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13.5px] font-medium transition-colors">
                            <i class="nav-icon {{ $item['icon'] }} text-[14px] shrink-0 w-5 text-center"></i>
                            <span class="nav-label whitespace-nowrap transition-all duration-200">{{ $item['label'] }}</span>
                        </a>
                    @endif
                    <span class="nav-tooltip">{{ $item['label'] }}</span>
                </div>
            @endforeach
        @endforeach
    </nav>

    {{-- Footer sidebar --}}
    <div class="sidebar-footer shrink-0 p-3 space-y-0.5">
        <button onclick="toggleSidebar()"
            class="collapse-btn hidden lg:flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-[13px] font-medium transition-colors">
            <i id="collapse-icon" class="fa-solid fa-angles-left text-[14px] shrink-0 w-5 text-center transition-transform duration-250"></i>
            <span class="nav-label whitespace-nowrap transition-all duration-200">Collapse</span>
        </button>

        <div class="user-row flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-colors">
            <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[11px] font-bold shrink-0">
                {{ Str::of(auth()->user()->name ?? 'Guest')->explode(' ')->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('') }}
            </div>
            <div class="sidebar-user-info overflow-hidden min-w-0">
                <p class="user-name text-[13px] font-medium whitespace-nowrap leading-tight">{{ auth()->user()->name ?? 'Guest' }}</p>
                <p class="user-role text-[11px] whitespace-nowrap leading-tight">{{ auth()->user()->role ?? 'Administrator' }}</p>
            </div>
            <i class="user-dots fa-solid fa-ellipsis-vertical text-xs ml-auto nav-label transition-all duration-200"></i>
        </div>
    </div>
</aside>
