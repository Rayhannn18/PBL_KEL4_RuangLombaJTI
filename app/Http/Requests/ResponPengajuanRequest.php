<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResponPengajuanRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:disetujui,ditolak'],
            'catatan' => ['required_if:status,ditolak', 'nullable', 'string', 'max:500'],
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
            'status.required' => 'Status persetujuan wajib ditentukan.',
            'status.in' => 'Status harus bernilai disetujui atau ditolak.',
            'catatan.required_if' => 'Alasan penolakan wajib diisi untuk memberikan penjelasan kepada tim mahasiswa.',
            'catatan.max' => 'Catatan alasan tidak boleh melebihi 500 karakter.',
        ];
    }
}
