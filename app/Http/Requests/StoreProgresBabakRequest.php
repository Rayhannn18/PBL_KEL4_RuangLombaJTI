<?php

namespace App\Http\Requests;

use App\Models\ProgresBabak;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgresBabakRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Otorisasi ketua tim dilakukan di controller (pesan galat dipertahankan).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Apakah babak yang dikirim adalah babak Final.
     */
    private function isBabakFinal(): bool
    {
        return (string) $this->input('babak') === ProgresBabak::BABAK_FINAL;
    }

    /**
     * Get the validation rules that apply to the request.
     * Berlaku untuk laporan BARU maupun perbaikan; data lama tidak divalidasi ulang.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isFinal = $this->isBabakFinal();
        $hasilOptions = $isFinal ? ProgresBabak::HASIL_FINAL : ProgresBabak::HASIL_NON_FINAL;

        $existing = $this->route('id') ? ProgresBabak::find($this->route('id')) : null;
        $sudahPunyaBukti = $existing && ! empty($existing->file_bukti);

        if ($isFinal) {
            $buktiRules = ProgresBabak::isHasilWajibBukti((string) $this->input('hasil_akhir')) && ! $sudahPunyaBukti
                ? ['required']
                : ['nullable'];
            $buktiRules = array_merge($buktiRules, ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048']);
            $keteranganRules = ['nullable', 'string', 'max:100'];
        } else {
            $buktiRules = ['prohibited'];
            $keteranganRules = ['prohibited'];
        }

        return [
            'id_pengajuan' => ['required', 'exists:pengajuan_bimbingan,id_pengajuan'],
            'babak' => ['required', 'string', Rule::in(ProgresBabak::BABAK_OPTIONS)],
            'babak_lainnya' => [
                Rule::requiredIf(fn () => (string) $this->input('babak') === ProgresBabak::BABAK_LAINNYA),
                'nullable',
                'string',
                'max:50',
            ],
            'tanggal_update' => ['required', 'date'],
            'hasil_akhir' => ['required', 'string', Rule::in($hasilOptions)],
            'keterangan_hasil' => $keteranganRules,
            'file_bukti' => $buktiRules,
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_pengajuan.required' => 'Pengajuan bimbingan wajib ditentukan.',
            'id_pengajuan.exists' => 'Pengajuan bimbingan tidak valid.',
            'babak.required' => 'Tahapan/babak kompetisi wajib dipilih.',
            'babak.in' => 'Tahapan/babak yang dipilih tidak valid.',
            'babak_lainnya.required' => 'Nama babak lainnya wajib diisi.',
            'babak_lainnya.max' => 'Nama babak lainnya maksimal 50 karakter.',
            'tanggal_update.required' => 'Tanggal capaian wajib diisi.',
            'tanggal_update.date' => 'Format tanggal tidak valid.',
            'hasil_akhir.required' => 'Hasil/status capaian wajib dipilih.',
            'hasil_akhir.in' => 'Hasil/status capaian tidak valid untuk babak yang dipilih.',
            'keterangan_hasil.max' => 'Keterangan hasil maksimal 100 karakter.',
            'keterangan_hasil.prohibited' => 'Keterangan hasil hanya dapat diisi untuk babak Final.',
            'file_bukti.required' => 'Bukti (PDF/JPG/PNG) wajib diunggah untuk hasil ini pada babak Final.',
            'file_bukti.prohibited' => 'Unggah bukti hanya tersedia untuk babak Final.',
            'file_bukti.file' => 'Bukti harus berupa berkas yang valid.',
            'file_bukti.mimes' => 'Bukti harus berformat PDF, JPG, atau PNG.',
            'file_bukti.max' => 'Ukuran bukti maksimal 2 MB.',
            'file_bukti.uploaded' => 'Bukti gagal diunggah. Pastikan ukuran berkas tidak melebihi 2 MB.',
        ];
    }
}
