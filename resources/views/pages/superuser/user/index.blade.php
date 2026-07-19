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

@endsection

@if (isset($success))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($success)
                ntAlert.success('Operasi berhasil dilakukan.', 'Berhasil');
            @else
                ntAlert.error('Operasi gagal dilakukan.', 'Gagal');
            @endif
        });
    </script>
@endif
