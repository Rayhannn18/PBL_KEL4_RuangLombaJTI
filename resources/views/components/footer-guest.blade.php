<footer class="site-footer-guest text-slate-200 border-t border-white/10 pt-12 pb-6 text-sm" style="background: linear-gradient(135deg, #0F2744 0%, #142F5B 50%, #1E3A8A 100%);">
    <div class="footer-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 3-Column Layout -->
        <div class="footer-grid grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 pb-8">
            <!-- Kolom 1 – Identitas Sistem -->
            <div class="footer-col-identitas md:col-span-12 lg:col-span-6">
                <a href="{{ route('dashboard.analitik') }}" class="footer-brand-title inline-flex items-center gap-3 text-white font-bold text-lg tracking-tight hover:opacity-95 transition-opacity mb-3">
                    <img src="{{ asset('images/logo-ruanglombajti.png') }}" 
                         alt="Logo RuangLombaJTI" 
                         class="w-8 h-8 object-contain"
                         style="width: 32px; height: 32px; filter: drop-shadow(0 0 8px rgba(96, 165, 250, 0.45));">
                    <span class="text-xl font-extrabold text-white">RuangLomba<span class="text-blue-400">JTI</span></span>
                </a>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-lg mt-2 mb-0" style="color: rgba(255, 255, 255, 0.8);">
                    Sistem Informasi Pengelolaan Lomba, Pembentukan Tim, dan Pembimbingan serta Monitoring Progres Lomba Mahasiswa Jurusan Teknologi Informasi Politeknik Negeri Malang.
                </p>
            </div>

            <!-- Kolom 2 – Navigasi Sistem -->
            <div class="footer-col-navigasi md:col-span-6 lg:col-span-3">
                <h4 class="footer-heading text-xs font-semibold uppercase tracking-wider text-white mb-3.5">
                    Navigasi Sistem
                </h4>
                <ul class="footer-nav-list space-y-2.5 text-sm list-none p-0 m-0">
                    <li>
                        <a href="{{ route('dashboard.analitik') }}" class="footer-nav-link text-slate-300 hover:text-white inline-flex items-center gap-2 transition-all duration-150">
                            <i class="bi bi-chevron-right text-[11px] text-blue-300"></i>
                            <span>Dashboard Analitik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('login') }}" class="footer-nav-link text-slate-300 hover:text-white inline-flex items-center gap-2 transition-all duration-150">
                            <i class="bi bi-chevron-right text-[11px] text-blue-300"></i>
                            <span>Masuk Sistem</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('register') }}" class="footer-nav-link text-slate-300 hover:text-white inline-flex items-center gap-2 transition-all duration-150">
                            <i class="bi bi-chevron-right text-[11px] text-blue-300"></i>
                            <span>Pendaftaran Akun</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Kolom 3 – Informasi -->
            <div class="footer-col-informasi md:col-span-6 lg:col-span-3">
                <h4 class="footer-heading text-xs font-semibold uppercase tracking-wider text-white mb-3.5">
                    Informasi
                </h4>
                <div class="space-y-2 text-xs sm:text-sm text-slate-300">
                    <p class="footer-info-item font-semibold text-white m-0">
                        Jurusan Teknologi Informasi
                    </p>
                    <p class="footer-info-item text-slate-300 m-0" style="color: rgba(255, 255, 255, 0.85);">
                        Politeknik Negeri Malang
                    </p>
                    <div class="footer-info-item flex items-start gap-2 pt-1 text-xs text-slate-300 leading-normal" style="color: rgba(255, 255, 255, 0.8);">
                        <i class="bi bi-geo-alt text-blue-400 mt-0.5 shrink-0"></i>
                        <span>Gedung Sipil &amp; JTI Polinema, Jl. Soekarno-Hatta No. 9, Malang.</span>
                    </div>

                    <!-- Tautan Resmi Terverifikasi -->
                    <div class="pt-2 flex flex-col gap-1.5 text-xs">
                        <a href="https://www.polinema.ac.id" target="_blank" rel="noopener noreferrer" 
                           class="footer-external-link inline-flex items-center gap-1.5 text-slate-300 hover:text-white transition-colors duration-150"
                           title="Buka Website Resmi Politeknik Negeri Malang">
                            <i class="bi bi-globe2 text-blue-300 text-[11px]"></i>
                            <span>Website Polinema</span>
                            <i class="bi bi-box-arrow-up-right text-[10px] text-blue-300/80 ml-0.5"></i>
                        </a>
                        <a href="https://jti.polinema.ac.id" target="_blank" rel="noopener noreferrer" 
                           class="footer-external-link inline-flex items-center gap-1.5 text-slate-300 hover:text-white transition-colors duration-150"
                           title="Buka Website Resmi Jurusan Teknologi Informasi Polinema">
                            <i class="bi bi-link-45deg text-blue-300 text-[13px]"></i>
                            <span>Website JTI Polinema</span>
                            <i class="bi bi-box-arrow-up-right text-[10px] text-blue-300/80 ml-0.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian Copyright -->
        <div class="footer-bottom-bar border-t border-white/10 pt-5 mt-2 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-center sm:text-left" style="color: rgba(255, 255, 255, 0.7);">
            <div>
                &copy; 2026 RuangLombaJTI &middot; Politeknik Negeri Malang. All rights reserved.
            </div>
            <div class="text-[11px]" style="color: rgba(255, 255, 255, 0.6);">
                Jurusan Teknologi Informasi &middot; Polinema
            </div>
        </div>
    </div>
</footer>
