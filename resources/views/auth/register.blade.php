@extends('layouts.auth')

@section('title', 'Daftar Akun')

@php
    $role = old('role', 'mahasiswa');
@endphp

@section('content')
    <h1 class="text-center text-[26px] font-extrabold tracking-tight">Buat Akun Baru</h1>
    <p class="mt-2 text-center text-sm text-muted">Pilih peranmu, lalu lengkapi data di bawah.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-5" novalidate>
        @csrf

        <fieldset>
            <legend class="mb-2 text-[13px] font-semibold text-muted">Pilih Peran Akun</legend>
            <div class="grid grid-cols-2 gap-3">
                @foreach ([
                    'mahasiswa' => ['Mahasiswa', 'Cari lomba, bentuk tim, ajukan bimbingan.'],
                    'dosen' => ['Dosen', 'Bimbing tim & kelola ketersediaan.'],
                ] as $value => [$title, $desc])
                    <label class="group relative cursor-pointer rounded-2xl border border-line bg-white p-4 transition has-checked:border-2 has-checked:border-brand has-checked:bg-brand-soft has-focus-visible:ring-4 has-focus-visible:ring-brand/20">
                        <input type="radio" name="role" value="{{ $value }}" class="sr-only" @checked($role === $value)>

                        <span class="absolute right-3 top-3 hidden size-5 items-center justify-center rounded-full bg-brand text-white group-has-checked:flex">
                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        </span>

                        <span class="flex size-8 items-center justify-center rounded-full bg-brand-soft text-brand group-has-checked:bg-white">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                @if ($value === 'mahasiswa')
                                    <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
                                @else
                                    <path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>
                                @endif
                            </svg>
                        </span>
                        <span class="mt-3 block text-[15px] font-bold text-ink">{{ $title }}</span>
                        <span class="mt-1 block text-xs leading-snug text-muted">{{ $desc }}</span>
                    </label>
                @endforeach
            </div>
            @error('role')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </fieldset>

        <x-auth.field name="name" label="Nama Lengkap" placeholder="cth. Muhammad Farhan" autocomplete="name" />
        <x-auth.field name="nim_nip" label="NIM" aside="Nomor Induk Mahasiswa POLINEMA" placeholder="cth. 2241720088" autocomplete="off" />
        <x-auth.field name="email" type="email" label="Email" placeholder="cth. 2241720088@student.polinema.ac.id"
                      autocomplete="email" check="email" />
        <x-auth.field name="password" type="password" label="Kata Sandi" placeholder="Minimal 8 karakter" autocomplete="new-password" />
        <x-auth.field name="password_confirmation" type="password" label="Konfirmasi Kata Sandi" placeholder="Ulangi kata sandi" autocomplete="new-password" />

        <div data-role-only="dosen" @class(['flex gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-[13px] leading-relaxed text-ink', 'hidden' => $role !== 'dosen'])>
            <svg class="mt-0.5 size-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <p><strong class="font-bold">Catatan Dosen:</strong> Bidang keahlian lomba &amp; kuota bimbingan mahasiswa dapat dikonfigurasi melalui menu Pengaturan Profil setelah akun aktif.</p>
        </div>

        <div>
            <label class="flex cursor-pointer items-start gap-3 text-[13px] leading-relaxed text-muted">
                <input type="checkbox" name="terms" value="1" @checked(old('terms')) class="mt-0.5 size-[18px] shrink-0 cursor-pointer rounded accent-brand">
                <span>Saya menyetujui <a href="#" class="font-semibold text-brand hover:underline">Syarat &amp; Ketentuan</a> serta <a href="#" class="font-semibold text-brand hover:underline">Kebijakan Privasi</a> RuangLombaJTI.</span>
            </label>
            @error('terms')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="flex h-[52px] w-full items-center justify-center gap-2 rounded-full bg-brand text-[15px] font-bold text-white shadow-lg shadow-brand/25 transition hover:bg-brand-dark focus:outline-none focus:ring-4 focus:ring-brand/30">
            Buat Akun
            <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
    </form>

    <div class="mt-7 border-t border-line pt-6 text-center text-sm text-muted">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="ml-1 font-bold text-brand hover:underline">Masuk di sini</a>
    </div>
@endsection