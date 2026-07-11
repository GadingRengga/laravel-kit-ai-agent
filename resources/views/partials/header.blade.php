<header class="sticky top-0 z-20 h-16 flex items-center px-4 lg:px-6 gap-2 shrink-0">

    <button onclick="openMobileSidebar()" class="topbar-icon-btn lg:hidden p-2 rounded-lg transition-colors">
        <i class="fa-solid fa-bars text-[16px]"></i>
    </button>
    <button onclick="toggleSidebar()" class="topbar-icon-btn hidden lg:flex p-2 rounded-lg transition-colors">
        <i class="fa-solid fa-bars text-[15px]"></i>
    </button>

    {{-- Breadcrumb: isi dari halaman dengan @section('breadcrumb') ... @endsection,
         fallback ke judul halaman saja kalau tidak diisi --}}
    <nav class="topbar-breadcrumb hidden sm:flex items-center text-[13px] font-medium ml-1 gap-1.5">
        @hasSection('breadcrumb')
            @yield('breadcrumb')
        @else
            <a href="{{ url('/') }}" class="hover:text-indigo-600 transition-colors">Home</a>
            <i class="fa-solid fa-chevron-right text-[8px]"></i>
            <span class="topbar-breadcrumb-active">@yield('title', 'Dashboard')</span>
        @endif
    </nav>

    <div class="ml-auto flex items-center gap-1">

        <div
            class="topbar-search hidden md:flex items-center gap-2 rounded-lg px-3 py-2 transition-all w-44 focus-within:w-60 mr-1">
            <i class="fa-solid fa-magnifying-glass text-[12px] topbar-search-icon"></i>
            <input type="text" placeholder="Search…"
                class="topbar-search-input bg-transparent border-none outline-none text-[13px] placeholder:text-[#B0B5D8] w-full" />
        </div>

        {{-- Notifications --}}
        <div class="relative" id="notif-wrap">
            <button onclick="toggleDropdown('notif')"
                class="topbar-icon-btn relative p-2.5 rounded-lg transition-colors">
                <i class="fa-regular fa-bell text-[16px]"></i><span class="notif-dot"></span>
            </button>
            <div id="notif-dropdown" class="dropdown-panel w-80">
                <div
                    class="px-4 py-3 border-b border-surface-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-[14px] font-semibold text-surface-900 dark:text-white">Notifications</span>
                    <span class="text-[11px] text-indigo-600 dark:text-indigo-400 cursor-pointer hover:underline">Mark
                        all read</span>
                </div>
                <div class="px-4 py-4 text-center text-[13px] text-surface-400">No new notifications</div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="relative" id="msg-wrap">
            <button onclick="toggleDropdown('msg')" class="topbar-icon-btn relative p-2.5 rounded-lg transition-colors">
                <i class="fa-regular fa-comment-dots text-[16px]"></i>
            </button>
            <div id="msg-dropdown" class="dropdown-panel w-72">
                <div class="px-4 py-3 border-b border-surface-200 dark:border-slate-700">
                    <span class="text-[14px] font-semibold text-surface-900 dark:text-white">Messages</span>
                </div>
                <div class="px-4 py-4 text-center text-[13px] text-surface-400">No new messages</div>
            </div>
        </div>

        <button onclick="toggleDark()" class="topbar-icon-btn p-2.5 rounded-lg transition-colors">
            <i class="fa-regular fa-moon text-[15px] dark:hidden"></i>
            <i class="fa-regular fa-sun text-[15px] hidden dark:block"></i>
        </button>

        <div class="topbar-divider w-px h-5 mx-0.5"></div>

        {{-- Account --}}
        <div class="relative" id="acct-wrap">
            <button onclick="toggleDropdown('acct')"
                class="topbar-icon-btn flex items-center gap-2 pl-1 pr-2 py-1.5 rounded-lg transition-colors">
                <div
                    class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[11px] font-bold">
                    {{ Str::of(auth()->user()->name ?? 'Guest')->explode(' ')->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('') }}
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-[13px] font-medium leading-none topbar-acct-name">
                        {{ auth()->user()->name ?? 'Guest' }}</p>
                    <p class="text-[11px] leading-none mt-0.5 topbar-acct-role">
                        {{ auth()->user()->role->name ?? 'Admin' }}
                    </p>
                </div>
                <i class="fa-solid fa-chevron-down text-[9px] hidden md:block ml-0.5 topbar-chevron"></i>
            </button>

            <div id="acct-dropdown" class="dropdown-panel w-52">
                <div class="px-4 py-3 border-b border-surface-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-full bg-brand-600 flex items-center justify-center text-white text-[12px] font-bold">
                            {{ Str::of(auth()->user()->name ?? 'Guest') }}
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-surface-900 dark:text-white leading-tight">
                                {{ auth()->user()->name ?? 'Guest' }}</p>
                            <p class="text-[11px] text-surface-400 leading-tight">{{ auth()->user()->email ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="py-1.5">
                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2.5 text-[13px] text-surface-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-800 transition-colors">
                        <i class="fa-regular fa-user text-indigo-400 w-4 text-center text-[13px]"></i>My Profile
                    </a>
                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2.5 text-[13px] text-surface-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-800 transition-colors">
                        <i class="fa-solid fa-gear text-indigo-400 w-4 text-center text-[13px]"></i>Settings
                    </a>
                </div>
                <div class="py-1.5 border-t border-surface-100 dark:border-slate-700">
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" live-spa="exclude"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-[13px] text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <i class="fa-solid fa-right-from-bracket w-4 text-center text-[13px]"></i>Sign out
                            </button>
                        </form>
                    @else
                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2.5 text-[13px] text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center text-[13px]"></i>Sign out
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
