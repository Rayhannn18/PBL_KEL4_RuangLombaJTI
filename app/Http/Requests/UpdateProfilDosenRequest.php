<?php

namespace App\Http\Requests;

use App\Models\Dosen;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilDosenRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pembaruan profil Dosen Pembimbing.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bidang_keahlian' => ['required', 'string', 'min:3', 'max:255'],
            'kuota_bimbingan' => ['required', 'integer', 'min:1', 'max:30'],
        ];
    }

    /**
     * Pesan kustom untuk aturan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bidang_keahlian.required' => 'Bidang keahlian dosen wajib diisi.',
            'bidang_keahlian.min' => 'Bidang keahlian minimal 3 karakter.',
            'bidang_keahlian.max' => 'Bidang keahlian tidak boleh lebih dari 255 karakter.',
            'kuota_bimbingan.required' => 'Kuota bimbingan wajib diisi.',
            'kuota_bimbingan.integer' => 'Kuota bimbingan harus berupa angka bulat.',
            'kuota_bimbingan.min' => 'Kuota bimbingan minimal 1 tim.',
            'kuota_bimbingan.max' => 'Kuota bimbingan maksimal 30 tim.',
        ];
    }

    /**
     * Validasi tambahan setelah aturan dasar:
     * Kuota baru tidak boleh lebih kecil dari jumlah bimbingan 'disetujui' yang sedang aktif.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $nidn = session('auth_user.id') ?? auth()->user()?->nim_nip ?? auth()->id();
            $dosen = Dosen::where('nidn', $nidn)->first();

            if ($dosen) {
                $aktifCount = $dosen->bimbinganAktif()->count();
                $kuotaBaru = (int) $this->input('kuota_bimbingan');

                if ($kuotaBaru < $aktifCount) {
                    $validator->errors()->add(
                        'kuota_bimbingan',
                        "Kuota bimbingan baru ({$kuotaBaru}) tidak boleh lebih kecil dari jumlah bimbingan aktif yang sedang disetujui ({$aktifCount} tim)."
                    );
                }
            }
        });
    }
}
