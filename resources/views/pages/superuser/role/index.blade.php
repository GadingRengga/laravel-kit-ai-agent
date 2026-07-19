@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Manajemen Role</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Kelola role & permission yang melekat pada setiap role.</p>
    </div>
@endsection

@section('content')
    <div id="role-panel" live-scope="Superuser.RoleController">
        @include('pages.superuser.role.partials._panel')
    </div>
    <script>
        window.alert = function(el, response) {
            if (response?.success) {
                ntAlert.success(response.message || 'User berhasil dihapus.', 'Berhasil');
            } else {
                ntAlert.error(response.message || 'Gagal menghapus user.', 'Gagal');
            }
        };
    </script>

@endsection
