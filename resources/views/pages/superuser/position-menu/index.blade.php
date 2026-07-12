@extends('layouts.app')

@section('title', 'Akses Menu per Posisi')

@section('page-title')
    <div>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight">Akses Menu per Posisi</h1>
        <p class="text-[13px] text-slate-400 mt-0.5">Atur menu apa saja & hak akses (lihat/tambah/ubah/hapus) yang
            didapat tiap posisi jabatan — mis. Departemen Marketing, Posisi Staff.</p>
    </div>
@endsection

@section('content')
    @include('pages.superuser.position-menu.partials._panel')
@endsection
