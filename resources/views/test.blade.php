@extends('layouts.app')

@section('title', 'Test')

@section('page-title')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Test</h1>
            <p class="text-[13px] text-indigo-400 dark:text-indigo-400 mt-0.5">
                Welcome back, {{ auth()->user()->name ?? 'Gading' }}. Here's what's happening today.
            </p>
        </div>
    </div>
@endsection

@section('content')
    <style>
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e5e5;
            border-top: 4px solid #4f46e5;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        .d-none {
            display: none !important;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
    <div id="test-container" live-scope="TrialController">

        @include('pages.test-dom')
    </div>
@endsection
