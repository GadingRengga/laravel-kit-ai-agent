@extends('layouts.app')

@section('title', 'Manajemen Menu')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Manajemen Menu</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Kelola struktur menu sidebar aplikasi — maksimal 2 level (menu
            utama & submenu).</p>
    </div>
@endsection

@section('content')
    <div id="menu-panel" live-scope="Superuser.MenuController">
        @include('pages.superuser.menu.partials._panel')
    </div>
    <script>
        window.alert = function(el, response) {
            if (response?.success) {
                ntAlert.success(response.message || 'Menu berhasil dihapus.', 'Berhasil');
            } else {
                ntAlert.error(response.message || 'Gagal memproses menu.', 'Gagal');
            }
        };
    </script>
@endsection
