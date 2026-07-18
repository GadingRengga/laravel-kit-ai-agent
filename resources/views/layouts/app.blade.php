<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-debug" content="{{ config('app.debug') ? 'true' : 'false' }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Netra UI') }}</title>

    {{-- Anti-flash dark mode, dijalankan sebelum CSS/JS lain --}}
    <script>
        (function() {
            const s = localStorage.getItem('theme');
            const isDark = s === 'dark' || (!s && window.matchMedia('(prefers-color-scheme:dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        })();
    </script>

    {{-- Font & icon CDN (boleh dipindah ke build pipeline nanti kalau mau) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    {{-- Semua CSS & JS project (app.css sudah meng-import netra-base.css & netra-buttons.css) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    {{-- Global Loading Overlay --}}
    <div id="nt-loading-overlay" class="nt-loading-overlay" style="display: none;">
        <div class="nt-loading-card">
            <span class="nt-spinner-dual nt-spin-lg"></span>
            <p class="nt-loading-text" data-nt-loading-text>Memuat…</p>
        </div>
    </div>

    {{-- Global Alert Container --}}
    <div id="nt-alert-container" class="nt-alert-container"></div>
</head>

<body class="h-full overflow-hidden font-sans antialiased">

    <div id="mob-overlay" class="fixed inset-0 bg-black/40 z-30 hidden opacity-0 lg:hidden"
        onclick="closeMobileSidebar()"></div>

    <div id="app">

        <div live-spa-region="sidebar">
            @include('partials.sidebar')
        </div>

        <div id="main-col">
            <div live-spa-region="header">
                @include('partials.header')
            </div>

            <div id="page-wrapper" live-spa-region="main">

                @hasSection('page-title')
                    <div class="page-title-bar px-4 lg:px-8 py-5 shrink-0">
                        @yield('page-title')
                    </div>
                @endif

                <main class="page-content">
                    @if (session('success'))
                        <x-alert tone="success" :title="session('success')" class="mb-4" />
                    @endif

                    @if (session('error'))
                        <x-alert tone="error" :title="session('error')" class="mb-4" />
                    @endif

                    @if (session('warning'))
                        <x-alert tone="warning" :title="session('warning')" class="mb-4" />
                    @endif

                    @if (session('info'))
                        <x-alert tone="info" :title="session('info')" class="mb-4" />
                    @endif

                    @yield('content')
                </main>

            </div>

            @include('partials.footer')

        </div>
    </div><!-- end app -->
    @include('ai.partials._floating-widget')
    @stack('scripts')
    <script>
        function initNetra(root = document) {
            if (!window.NetraUI) return;

            NetraUI.initSelect(root);
            NetraUI.initDatepicker(root);
            NetraUI.initTimepicker(root);
            NetraUI.initFileUpload(root);
            NetraUI.initColorPicker(root);

            NetraUI.initTable(root);

            NetraUI.initTreeTable(root);
            NetraUI.initCollapseTable(root);
            NetraUI.initEditableTable(root);
            NetraUI.initGanttTable(root);
            NetraUI.initBasicTable(root);
        }

        document.addEventListener('live-dom:afterUpdate', function(e) {
            initNetra(e.target);
            handleLiveLoading(e.target);
            handleLiveCallbackAfter(e.target);
        });

        document.addEventListener('live-dom:afterSpa', function(e) {
            initNetra(e.target);
            handleLiveLoading(e.target);
            handleLiveCallbackAfter(e.target);
            // setActiveMenu(e.detail?.url);
            // initNavActive();
            // restoreSidebarCollapsedState();
        });

        window.liveDomConfig = {
            spaExcludePrefixes: ['/logout']
        };
    </script>
</body>

</html>
