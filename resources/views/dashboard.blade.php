@extends('layouts.auth')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-center text-[26px] font-extrabold tracking-tight">Halo, {{ auth()->user()->name }}</h1>
    <p class="mt-2 text-center text-sm text-muted">
        Anda masuk sebagai <strong class="text-ink">{{ auth()->user()->role instanceof \App\Enums\UserRole ? auth()->user()->role->label() : ucfirst(auth()->user()->role) }}</strong>
        @if (auth()->user()->nim_nip)
            ({{ auth()->user()->nim_nip }})
        @endif
    </p>
    @if (auth()->user()->prodi)
        <p class="mt-1 text-center text-sm text-muted">
            {{ auth()->user()->prodi }} &middot; Angkatan {{ auth()->user()->angkatan }}
        </p>
    @endif
    <p class="mt-1 text-center text-xs text-muted">Halaman sementara — dashboard sesungguhnya menyusul.</p>

    <form method="POST" action="{{ route('logout') }}" class="mt-7">
        @csrf
        <button type="submit"
                class="flex h-[52px] w-full items-center justify-center rounded-full border border-line bg-white text-[15px] font-bold text-ink transition hover:bg-page">
            Keluar
        </button>
    </form>
@endsection