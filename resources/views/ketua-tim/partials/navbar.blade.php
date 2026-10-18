<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <a href="{{ route('dashboard.analitik') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-lg bg-blue-700 text-white flex items-center justify-center font-black text-base shadow-sm group-hover:bg-blue-800 transition">
                    R
                </div>
                <div class="flex flex-col">
                    <span class="text-base font-extrabold tracking-tight text-slate-900 leading-none">
                        RuangLomba<span class="text-blue-700">JTI</span>
                    </span>
                    <span class="text-[10px] font-medium text-gray-400 tracking-tight mt-0.5">
                        Sistem Informasi &amp; Direktori Prestasi Mahasiswa
                    </span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('dashboard.analitik') }}" class="text-sm font-medium text-gray-600 hover:text-blue-700 transition">
                    Dashboard Analitik
                </a>
                <a href="#" class="text-sm font-medium text-gray-600 hover:text-blue-700 transition">
                    Modul Bimbingan
                </a>
                <a href="{{ route('ketua-tim.modul-lomba') }}" class="inline-flex items-center bg-[#1e3a8a] text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm hover:bg-[#172e6b] transition">
                    Modul Lomba
                </a>
                <form action="{{ route('auth.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center bg-[#dc2626] hover:bg-[#b91c1c] text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                        Keluar
                    </button>
                </form>
            </nav>

            <div class="md:hidden flex items-center gap-2">
                <a href="{{ route('ketua-tim.modul-lomba') }}" class="bg-[#1e3a8a] text-white text-xs font-semibold px-3 py-1.5 rounded-md">
                    Modul Lomba
                </a>
                <form action="{{ route('auth.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-[#dc2626] text-white text-xs font-semibold px-3 py-1.5 rounded-md">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
