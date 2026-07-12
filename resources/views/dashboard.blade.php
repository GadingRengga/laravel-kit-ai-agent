@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="text-[13px] text-indigo-400 dark:text-indigo-400 mt-0.5">
                Welcome back, {{ auth()->user()->name ?? 'Gading' }}. Here's what's happening today.
            </p>
        </div>
    </div>
@endsection

@section('content')
    <div>

    </div>
@endsection
