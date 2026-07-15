@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Manajemen Role</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Kelola role & permission yang melekat pada setiap role.</p>
    </div>
@endsection

@section('content')
    @include('pages.superuser.role.partials._panel')
@endsection
