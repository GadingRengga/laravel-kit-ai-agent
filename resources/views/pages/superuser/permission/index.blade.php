@extends('layouts.app')

@section('title', 'Manajemen Permission')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Manajemen Permission</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Kelola permission & tautkan ke menu yang relevan.</p>
    </div>
@endsection

@section('content')
    <div id="permission-panel" live-scope="Superuser.PermissionController">
        @include('pages.superuser.permission.partials._panel')
    </div>
    <script>
        window.alert = function(el, response) {
            if (response?.success) {
                ntAlert.success(response.message || 'Permission berhasil dihapus.', 'Berhasil');
            } else {
                ntAlert.error(response.message || 'Gagal memproses permission.', 'Gagal');
            }
        };
    </script>
@endsection
