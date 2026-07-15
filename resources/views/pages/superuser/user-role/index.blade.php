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
    @include('pages.superuser.user-role.partials._panel')
@endsection
