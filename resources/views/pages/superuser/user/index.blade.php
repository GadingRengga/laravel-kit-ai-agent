@extends('layouts.app')

@section('title', 'Manajemen User')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Manajemen User</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Kelola akun user, role, dan status aktif.</p>
    </div>
@endsection

@section('content')
    @include('pages.superuser.user.partials._panel')
@endsection
