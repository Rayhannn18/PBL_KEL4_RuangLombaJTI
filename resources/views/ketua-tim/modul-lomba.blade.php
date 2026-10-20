@extends('layouts.app')

@section('title')Modul Lomba - {{ $lomba['nama'] ?? 'Ketua Tim' }}@endsection

@push('styles')
    {{-- Inter font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Tailwind CDN (hanya untuk halaman ini) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            background-color: #f1f4f9 !important;
        }
        .container { max-width: 1280px !important; }
    </style>
@endpush

@section('content')
<div class="space-y-6">

    <div class="bg-[#eafaf1] border border-[#a2e6be] rounded-xl px-4 py-3 flex items-center gap-3 text-sm text-[#1e5837] shadow-sm">
        <span class="text-base"></span>
        <p class="font-normal">
            Selamat datang kembali, Ketua Tim <strong class="font-bold text-[#144828]">{{ $akun['nama'] }}</strong>.
        </p>
    </div>

    @if(!empty($isEmpty) || !$lomba)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 bg-white rounded-2xl p-8 sm:p-10 border border-gray-200/80 shadow-sm text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-4">
                    
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">
                    Belum Ada Lomba yang Diikuti
                </h2>
                <p class="text-sm text-gray-500 max-w-md mx-auto mb-6 leading-relaxed">
                    Anda saat ini belum terdaftar sebagai ketua pada tim yang aktif atau belum memilih kompetisi lomba resmi di portal RuangLombaJTI.
                </p>
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 text-xs font-medium rounded-lg">
                    <span>ℹ️</span> Silakan daftarkan tim Anda atau hubungi Admin JTI untuk proses verifikasi.
                </div>
            </div>

            <div class="lg:col-span-1">
                @include('ketua-tim.partials.status-akun', ['akun' => $akun])
            </div>
        </div>
    @else

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">

            <div class="lg:col-span-2 bg-gradient-to-r from-[#173a82] via-[#1a4498] to-[#254ea8] text-white rounded-2xl p-6 sm:p-7 shadow-md flex flex-col justify-between border border-blue-800 relative overflow-hidden">
                <div>

                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-[#0f2759]/75 text-blue-200 border border-blue-400/30 w-fit mb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-300"></span>
                        <span>{{ $lomba['badge_role'] }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">
                        {{ $lomba['nama'] }}
                    </h1>

                    <p class="text-blue-100 text-xs sm:text-sm leading-relaxed mb-6 max-w-xl">
                        {{ $lomba['deskripsi_hero'] }}
                    </p>

                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#0f2759]/80 border border-blue-400/30 text-blue-100 shadow-sm">
                            <span></span>
                            <span>{{ $lomba['tingkat'] }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#0d453f]/75 border border-teal-400/40 text-teal-200 shadow-sm">
                            <span>✓</span>
                            <span>{{ $lomba['status_verifikasi'] }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#143272]/80 border border-blue-400/30 text-blue-200 shadow-sm">
                            <span></span>
                            <span>{{ $lomba['divisi_terpilih'] }}</span>
                        </span>
                    </div>
                </div>

                <div class="pt-4 border-t border-blue-400/20">
                    <div class="flex items-center justify-between text-xs text-blue-200 mb-2 font-medium">
                        <span>Progres tahapan lomba</span>
                        <span>Tahap {{ $lomba['tahap_nomor'] }} dari {{ $lomba['tahap_total'] }}</span>
                    </div>
                    <div class="w-full bg-[#0d224d] h-2 rounded-full overflow-hidden">
                        <div class="bg-amber-400 h-2 rounded-full transition-all duration-500" style="width: {{ ($lomba['tahap_nomor'] / max(1, $lomba['tahap_total'])) * 100 }}%"></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                @include('ketua-tim.partials.status-akun', ['akun' => $akun])
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            <div class="bg-white rounded-xl p-4 border border-gray-200/80 shadow-sm flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-600 flex items-center justify-center text-lg shrink-0">
                    🏆
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">
                        {{ Str::limit($lomba['nama'], 12) }}
                    </h4>
                    <p class="text-xs text-gray-400 font-normal mt-0.5">
                        Lomba diikuti
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-gray-200/80 shadow-sm flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200/60 text-blue-600 flex items-center justify-center text-lg shrink-0">
                    👥
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">
                        {{ $lomba['kuota_anggota'] }}
                    </h4>
                    <p class="text-xs text-gray-400 font-normal mt-0.5">
                        Kuota anggota
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-gray-200/80 shadow-sm flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    📝
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">
                        {{ Str::limit($lomba['tahap_saat_ini'], 14) }}
                    </h4>
                    <p class="text-xs text-gray-400 font-normal mt-0.5">
                        Tahap saat ini
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-gray-200/80 shadow-sm flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200/60 text-purple-600 flex items-center justify-center text-lg shrink-0">
                    🎓
                </div>
                <div>
                    <h4 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">
                        {{ $lomba['dosen_pembimbing'] }}
                    </h4>
                    <p class="text-xs text-gray-400 font-normal mt-0.5">
                        Pembimbing
                    </p>
                </div>
            </div>
        </div>

        {{-- BAGIAN 1: DETAIL LOMBA --}}
        <section id="detail-lomba" class="bg-white rounded-2xl p-6 sm:p-7 border border-gray-200/80 shadow-sm scroll-mt-20">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">1</div>
                <h2 class="text-lg font-bold text-gray-900">Detail Lomba</h2>
            </div>

            <p class="text-sm text-gray-500 leading-relaxed mb-5">{{ $lomba['deskripsi_lengkap'] }}</p>

            @php
                $themeMap = [
                    'red'   => 'bg-red-100 text-red-600',
                    'green' => 'bg-emerald-100 text-emerald-700',
                    'gray'  => 'bg-gray-200 text-gray-600',
                ];
                $stTheme = $themeMap[$lomba['status_waktu']['theme'] ?? 'gray'] ?? $themeMap['gray'];

                $infos = [
                    ['Penyelenggara',     $lomba['penyelenggara']],
                    ['Tingkat Lomba',     $lomba['tingkat']],
                    ['Kategori Minat',    $lomba['kategori']],
                    ['Biaya Pendaftaran', $lomba['biaya']],
                    ['Tipe Peserta',      $lomba['tipe_peserta']],
                    ['Mode Pelaksanaan',  $lomba['mode']],
                    ['Batas Tenggat',     $lomba['batas_tenggat']],
                    ['Status Waktu',      null],
                    ['Ukuran Tim',        $lomba['ukuran_tim']],
                    ['Pengusul',          $lomba['pengusul']],
                ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($infos as $info)
                    <div class="bg-slate-100 rounded-xl px-4 py-3.5">
                        <p class="text-[10.5px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">{{ $info[0] }}</p>
                        @if($info[0] === 'Status Waktu')
                            <span class="inline-block text-[11px] font-semibold px-2.5 py-0.5 rounded-full {{ $stTheme }}">
                                {{ $lomba['status_waktu']['label'] }}
                            </span>
                        @else
                            <p class="text-sm font-bold text-gray-900 leading-snug">{{ $info[1] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- BAGIAN 2: PERSYARATAN & JADWAL --}}
        <section id="persyaratan-jadwal" class="bg-white rounded-2xl p-6 sm:p-7 border border-gray-200/80 shadow-sm scroll-mt-20">

            <div class="flex items-center gap-3 mb-5">
                <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">2</div>
                <h2 class="text-lg font-bold text-gray-900">Persyaratan &amp; Jadwal</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- Persyaratan --}}
                <div>
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Persyaratan Peserta</h3>

                    <div class="space-y-2.5">
                        @forelse($lomba['persyaratan'] as $req)
                            <div class="bg-slate-100 rounded-xl px-4 py-3 flex items-start gap-3">
                                <span class="text-emerald-600 font-bold text-sm shrink-0 leading-snug">✓</span>
                                <p class="text-xs sm:text-sm text-gray-700 leading-snug">{{ $req }}</p>
                            </div>
                        @empty
                            <div class="text-xs text-gray-500 italic p-3 bg-gray-50 rounded-lg">
                                Persyaratan resmi mengikuti buku pedoman lomba.
                            </div>
                        @endforelse
                    </div>

                    <p class="text-[11px] text-gray-400 italic mt-3.5">
                        *Ketentuan resmi mengikuti buku pedoman.
                    </p>
                </div>

                {{-- Jadwal --}}
                <div>
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Jadwal Lomba</h3>

                    <div class="relative space-y-5">
                        <div class="absolute left-3 top-3 bottom-3 w-px bg-gray-200"></div>

                        @forelse($lomba['jadwal'] as $item)
                            @php
                                $dot = match($item['theme'] ?? '') {
                                    'amber' => 'bg-amber-500',
                                    'green' => 'bg-emerald-600',
                                    default => 'bg-blue-600',
                                };
                            @endphp
                            <div class="relative flex items-start gap-4">
                                <div class="relative z-10 w-6 h-6 rounded-full {{ $dot }} text-white font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $item['nomor'] }}
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900 leading-tight">{{ $item['tahap'] }}</span>
                                        @if(!empty($item['status']))
                                            <span class="bg-amber-100 text-amber-800 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                                                {{ ucfirst($item['status']) }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['waktu'] }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-gray-500 italic p-3 bg-gray-50 rounded-lg">
                                Jadwal tahapan belum dipublikasikan oleh panitia.
                            </div>
                        @endforelse
                    </div>

                    <p class="text-[11px] text-gray-400 italic mt-3.5">
                        *Jadwal dan status tahapan diperbarui otomatis berdasarkan tanggal sistem.
                    </p>
                </div>
            </div>
        </section>

        {{-- BAGIAN 3: TAUTAN PENDAFTARAN --}}
        <section id="tautan-pendaftaran" class="bg-gradient-to-r from-[#121f3a] via-[#142647] to-[#121f3a] rounded-2xl p-6 sm:p-7 text-white shadow-md border border-blue-900/60 scroll-mt-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-7 h-7 rounded-full bg-blue-800 text-white flex items-center justify-center font-bold text-sm shrink-0 border border-blue-500/40">
                            3
                        </div>
                        <h2 class="text-lg font-bold text-white">
                            Tautan Pendaftaran
                        </h2>
                    </div>

                    <p class="text-xs sm:text-sm text-blue-200/90 leading-relaxed mb-4">
                        Pendaftaran tim dilakukan ketua tim di portal resmi penyelenggara setelah tim disetujui admin dan dosen pembimbing.
                    </p>

                    <div class="bg-[#1b2c52]/90 border border-blue-800/60 rounded-xl px-4 py-3 text-xs sm:text-sm font-mono text-blue-200 select-all mb-4 break-all">
                        {{ $lomba['url_pendaftaran'] }}
                    </div>

                    <a href="{{ $lomba['url_pendaftaran'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-[#f59e0b] hover:bg-[#d97706] text-gray-950 font-bold px-5 py-2.5 rounded-xl text-xs sm:text-sm transition shadow-md">
                        <span>Buka Pendaftaran</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

                <div class="space-y-3">
                    @foreach($lomba['panduan_pendaftaran'] as $idx => $step)
                        <div class="bg-[#18294a]/80 border border-blue-800/40 rounded-xl p-3.5 flex items-center gap-3.5 shadow-xs">
                            <div class="w-5 h-5 rounded-full bg-[#f59e0b] text-gray-950 font-black text-xs flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </div>
                            <p class="text-xs sm:text-sm text-slate-100 font-medium leading-snug">
                                {{ $step }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- BAGIAN 4: BUKU PEDOMAN --}}
        <section id="buku-pedoman" class="bg-white rounded-2xl p-6 sm:p-7 border border-gray-200/80 shadow-sm scroll-mt-20">

            <div class="flex items-center gap-3 mb-5">
                <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    4
                </div>
                <h2 class="text-lg font-bold text-gray-900">
                    Buku Pedoman
                </h2>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 sm:gap-6">

                <div class="w-28 h-36 rounded-xl bg-gradient-to-b from-[#163578] to-[#0c1c3f] text-white p-3 flex flex-col justify-between shadow-md border border-blue-900/60 shrink-0 select-none">
                    <span class="text-[9px] font-bold tracking-widest text-blue-300 uppercase">PEDOMAN</span>
                    <div>
                        <p class="text-sm font-extrabold text-white leading-tight">GEMASTIK</p>
                        <p class="text-sm font-extrabold text-white leading-tight">XIX</p>
                    </div>
                    <span class="bg-[#dc2626] text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-xs self-end">
                        PDF
                    </span>
                </div>

                <div class="flex-1">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1">
                        {{ $lomba['pedoman']['judul'] }}
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-4 max-w-xl">
                        {{ $lomba['pedoman']['deskripsi'] }}
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ asset('storage/' . $lomba['pedoman']['path_pdf']) }}" target="_blank" class="inline-flex items-center gap-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-semibold px-4 py-2 rounded-xl text-xs sm:text-sm transition shadow-xs">
                            <span></span>
                            <span>Lihat</span>
                        </a>
                        <a href="{{ asset('storage/' . $lomba['pedoman']['path_pdf']) }}" download class="inline-flex items-center gap-2 bg-[#173a82] hover:bg-[#122e69] text-white font-semibold px-4 py-2 rounded-xl text-xs sm:text-sm transition shadow-xs">
                            <span></span>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

</div>
@endsection