@extends('layouts.app')

@section('title', 'Role User')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Role User</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Atur role yang dimiliki setiap user. Satu user bisa memiliki banyak
            role.</p>
    </div>
@endsection

@section('content')
    <div id="user-role-panel" live-scope="Superuser.UserRoleController">
        @include('pages.superuser.user-role.partials._panel')
    </div>
    <script>
        window.alert = function(el, response) {
            if (response?.success) {
                ntAlert.success(response.message || 'Role berhasil disimpan.', 'Berhasil');
            } else {
                ntAlert.error(response.message || 'Gagal menyimpan role user.', 'Gagal');
            }
        };
    </script>
@endsection
