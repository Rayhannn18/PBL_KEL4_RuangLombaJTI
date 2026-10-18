<footer class="bg-white border-t border-gray-200 mt-12 py-8 text-slate-600">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-700 text-white flex items-center justify-center font-black text-sm">
                    R
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900 leading-tight">
                        RuangLomba<span class="text-blue-700">JTI</span>
                    </p>
                    <p class="text-xs text-gray-400">
                        Sistem Informasi Monitoring Lomba dan Prestasi Mahasiswa Jurusan Teknologi Informasi Polinema
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-6 text-xs text-gray-500">
                <a href="{{ route('dashboard.analitik') }}" class="hover:text-blue-700 transition">Dashboard Analitik</a>
                <a href="{{ route('ketua-tim.modul-lomba') }}" class="hover:text-blue-700 transition">Modul Lomba</a>
                <span>&copy; {{ date('Y') }} Politeknik Negeri Malang</span>
            </div>
        </div>
    </div>
</footer>
