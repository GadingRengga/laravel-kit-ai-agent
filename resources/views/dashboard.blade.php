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
    <div live-scope="TrialController">
        <x-card title="Daftar Pengguna">
            <x-wizard style="vertical">
                <x-wizard.step title="Akun" subtitle="Informasi dasar" icon="fa-solid fa-user">
                    <div class="form-group">
                        <label class="form-label">Nama</label>
                        <input class="form-input" name="name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input class="form-input" type="email" name="email" required>
                    </div>
                </x-wizard.step>

                <x-wizard.step title="Profil" subtitle="Detail tambahan" icon="fa-solid fa-id-card" optional>
                    <div class="form-group">
                        <label class="form-label">Bio</label>
                        <textarea class="form-textarea" name="bio"></textarea>
                    </div>
                </x-wizard.step>

                <x-wizard.step title="Review" subtitle="Periksa kembali" icon="fa-solid fa-check-double">
                    <div class="nt-wizard-review-group">
                        <div class="nt-wizard-review-item">
                            <span>Nama</span>
                            <strong data-review-field="name"></strong>
                        </div>
                    </div>
                </x-wizard.step>
            </x-wizard>

            <x-button href="/test" icon-trailing="fa-solid fa-download">Test</x-button>
        </x-card>
    </div>
@endsection
