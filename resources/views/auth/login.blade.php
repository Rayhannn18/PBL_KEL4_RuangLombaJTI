@extends('layouts.auth')

@section('title', 'Masuk')

@section('header-action')
    <nav class="flex items-center rounded-full bg-white p-1 text-sm font-semibold shadow-sm ring-1 ring-line/70">
        <a href="{{ route('login') }}" class="rounded-full bg-brand px-4 py-2 text-white">Masuk</a>
        <a href="{{ route('register') }}" class="rounded-full px-4 py-2 text-muted transition hover:text-ink">Daftar Akun</a>
    </nav>
@endsection

@php
    $role = old('role', 'mahasiswa');
@endphp

@section('content')
    <h1 class="text-center text-[26px] font-extrabold tracking-tight">Masuk ke Akun</h1>
    <p class="mt-2 text-center text-sm text-muted">Pilih peranmu, lalu masukkan akun untuk melanjutkan.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5" novalidate>
        @csrf

        <fieldset>
            <legend class="mb-2 text-[13px] font-semibold text-muted">Masuk Sebagai</legend>
            <div class="grid grid-cols-3 gap-2.5">
                @foreach ([
                    'mahasiswa' => 'Mahasiswa',
                    'dosen' => 'Dosen',
                    'admin' => 'Admin',
                ] as $value => $title)
                    <label class="group relative flex cursor-pointer flex-col items-center gap-2 rounded-2xl border border-line bg-white px-2 py-3.5 text-center transition has-checked:border-2 has-checked:border-brand has-checked:bg-brand-soft has-focus-visible:ring-4 has-focus-visible:ring-brand/20">
                        <input type="radio" name="role" value="{{ $value }}" class="sr-only" @checked($role === $value)>

                        <span class="absolute right-2 top-2 hidden size-4 items-center justify-center rounded-full bg-brand text-white group-has-checked:flex">
                            <svg class="size-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>

                        <span class="flex size-8 items-center justify-center rounded-full bg-brand-soft text-brand group-has-checked:bg-white">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                @if ($value === 'mahasiswa')
                                    <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
                                @elseif ($value === 'dosen')
                                    <path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>
                                @else
                                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>
                                @endif
                            </svg>
                        </span>
                        <span class="text-[13px] font-bold text-ink">{{ $title }}</span>
                    </label>
                @endforeach
            </div>
            @error('role')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </fieldset>

        <x-auth.field name="login" label="Email / NIM" placeholder="cth. 2241720088 atau nama@gmail.com"
                      autocomplete="username" check="login" />

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

    {{-- Admin tidak mendaftar lewat form, jadi tautan daftar disembunyikan saat Admin dipilih. --}}
    <div data-role-only="mahasiswa,dosen" @class(['mt-7 border-t border-line pt-6 text-center text-sm text-muted', 'hidden' => $role === 'admin'])>
        Belum punya akun?
        <a href="{{ route('register') }}" class="ml-1 font-bold text-brand hover:underline">Daftar di sini</a>
    </div>
@endsection
