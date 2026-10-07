@extends('layouts.auth')

@section('title', 'Masuk')

@section('header-action')
    <nav class="flex items-center rounded-full bg-white p-1 text-sm font-semibold shadow-sm ring-1 ring-line/70">
        <a href="{{ route('login') }}" class="rounded-full bg-brand px-4 py-2 text-white">Masuk</a>
        <a href="{{ route('register') }}" class="rounded-full px-4 py-2 text-muted transition hover:text-ink">Daftar Akun</a>
    </nav>
@endsection

@section('content')
    <h1 class="text-center text-[26px] font-extrabold tracking-tight">Masuk ke Akun</h1>
    <p class="mt-2 text-center text-sm text-muted">Gunakan akun kampus untuk melanjutkan.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5" novalidate>
        @csrf

        <x-auth.field name="login" label="Email / NIM / NIP" placeholder="cth. 2241720088@student.polinema.ac.id"
                      autocomplete="username" check="login">
            <p class="mt-2 flex items-center gap-2 text-xs text-muted">
                <svg class="size-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 15h-2v-6h2v6Zm0-8h-2V7h2v2Z" clip-rule="evenodd"/></svg>
                Sistem otomatis mendeteksi role Mahasiswa atau Dosen.
            </p>
        </x-auth.field>

        <div>
            <x-auth.field name="password" type="password" label="Kata Sandi" placeholder="Masukkan kata sandi" autocomplete="current-password" />
            <div class="mt-3 text-right">
                <a href="#" class="text-[13px] font-semibold text-brand hover:underline">Lupa password?</a>
            </div>
        </div>

        <button type="submit"
                class="flex h-[52px] w-full items-center justify-center gap-2 rounded-full bg-brand text-[15px] font-bold text-white shadow-lg shadow-brand/25 transition hover:bg-brand-dark focus:outline-none focus:ring-4 focus:ring-brand/30">
            Masuk
            <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
    </form>

    <div class="mt-7 border-t border-line pt-6 text-center text-sm text-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" class="ml-1 font-bold text-brand hover:underline">Daftar di sini</a>
    </div>
@endsection