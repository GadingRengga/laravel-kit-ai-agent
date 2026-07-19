@extends('layouts.app')

@section('title', 'Manajemen User')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Manajemen User</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Kelola akun user, role, dan status aktif.</p>
    </div>
@endsection

@section('content')
    <div id="user-panel" live-scope="Superuser.UserController">
        @include('pages.superuser.user.partials._panel')
    </div>
    <script>
        window.alert = function(el, response) {
            if (response?.success) {
                ntAlert.success(response.message || 'User berhasil dihapus.', 'Berhasil');
            } else {
                ntAlert.error(response.message || 'Gagal memproses user.', 'Gagal');
            }
        };
    </script>
@endsection
