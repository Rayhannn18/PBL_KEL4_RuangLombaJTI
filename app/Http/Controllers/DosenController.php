<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilDosenRequest;
use App\Models\Dosen;
use App\Services\DosenRecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosenController extends Controller
{
    /**
     * Tampilkan formulir profil Dosen Pembimbing.
     */
    public function profil(Request $request): View|RedirectResponse
    {
        $nidn = session('auth_user.id') ?? auth()->user()?->nim_nip ?? auth()->id();
        $dosen = Dosen::with(['bimbinganAktif.tim.lomba'])->where('nidn', $nidn)->first();

        if (! $dosen) {
            return redirect()->route('dashboard')->with('error', 'Data Dosen Pembimbing tidak ditemukan.');
        }

        $bimbinganAktif = $dosen->bimbinganAktif;

        return view('dosen.profil', [
            'dosen' => $dosen,
            'bimbinganAktif' => $bimbinganAktif,
        ]);
    }

    /**
     * Simpan pembaruan profil Dosen Pembimbing (kuota & bidang keahlian).
     */
    public function updateProfil(UpdateProfilDosenRequest $request): RedirectResponse
    {
        $nidn = session('auth_user.id') ?? auth()->user()?->nim_nip ?? auth()->id();
        $dosen = Dosen::where('nidn', $nidn)->firstOrFail();

        $dosen->update([
            'bidang_keahlian' => $request->validated('bidang_keahlian'),
            'kuota_bimbingan' => (int) $request->validated('kuota_bimbingan'),
        ]);

        // Perbarui session auth_user agar sinkron dengan data profil terbaru
        if (session()->has('auth_user')) {
            $authUser = session('auth_user');
            $authUser['extra'] = $dosen->bidang_keahlian;
            session(['auth_user' => $authUser]);
        }

        // Hapus cache rekomendasi AI secara terarah agar rekomendasi mahasiswa memakai data terbaru
        DosenRecommendationService::clearAllRecommendationsCache();

        return back()->with('success', 'Profil dan kuota bimbingan berhasil diperbarui.');
    }
}
