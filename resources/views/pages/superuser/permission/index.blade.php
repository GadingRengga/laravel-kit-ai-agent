@extends('layouts.app')

@section('title', 'Manajemen Permission')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Manajemen Permission</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Kelola permission & tautkan ke menu yang relevan.</p>
    </div>
@endsection

@section('content')
    @include('pages.superuser.permission.partials._panel')
@endsection
