<div class="bg-gradient-to-br from-[#131f38] via-[#101a2e] to-[#0c1424] text-white rounded-2xl p-6 shadow-md border border-slate-700/60 flex flex-col justify-between h-full min-h-[220px]">
    <!-- Top Header -->
    <div class="flex items-center justify-between">
        <span class="text-xs font-bold tracking-wider text-slate-300 uppercase">
            STATUS AKUN MASUK
        </span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#063c26] text-[#34d399] border border-[#059669]/40 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-[#10b981] animate-pulse"></span>
            {{ $akun['status_siakad'] }}
        </span>
    </div>

    <div class="my-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-slate-800/90 border border-slate-700/90 flex items-center justify-center text-slate-300 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-white tracking-tight leading-snug">
                    {{ $akun['nama'] }}
                </h3>
                <p class="text-xs text-slate-400 mt-0.5 font-medium">
                    NIM. {{ $akun['nim'] }}
                </p>
            </div>
        </div>

        <div class="mt-3.5">
            <p class="text-xs sm:text-sm font-semibold text-slate-200">
                {{ $akun['prodi'] }}
            </p>
            <p class="text-xs text-slate-400 mt-0.5 font-normal">
                (Tingkat {{ $akun['tingkat'] }})
            </p>
        </div>
    </div>

    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white shadow-sm border border-blue-400/30">
            <span>⭐</span>
            {{ $akun['jenis_mahasiswa'] }}
        </span>
        <span class="text-xs text-slate-400 font-medium">
            Angkatan {{ $akun['angkatan'] }}
        </span>
    </div>
</div>
