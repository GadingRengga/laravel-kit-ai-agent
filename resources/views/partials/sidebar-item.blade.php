@php
    $hasChildren = !empty($item['children']);
    $isActive = !$hasChildren && request()->is($item['route'] ?? '');
    $hasActiveChild =
        $hasChildren &&
        collect($item['children'])->contains(fn($child) => request()->is($child['route'] ?? '__none__'));
@endphp

<div class="nav-item relative {{ $isActive ? 'active' : '' }} {{ $hasActiveChild ? 'has-active' : '' }}">
    @if ($hasChildren)
        <a href="#" onclick="toggleSubmenu(this,event)"
            class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13.5px] font-medium transition-colors">
            <i class="nav-icon {{ $item['icon'] }} text-[14px] shrink-0 w-5 text-center"></i>
            <span class="nav-label whitespace-nowrap transition-all duration-200">{{ $item['label'] }}</span>
            <i class="chevron {{ $hasActiveChild ? 'open' : '' }} fa-solid fa-chevron-right text-[10px] ml-auto"></i>
        </a>

        <div class="submenu {{ $hasActiveChild ? 'open' : '' }} mt-1 space-y-0.5">
            @foreach ($item['children'] as $child)
                @include('partials.sidebar-item', ['item' => $child])
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
