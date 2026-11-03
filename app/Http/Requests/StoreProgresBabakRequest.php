<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProgresBabakRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_pengajuan' => ['required', 'exists:pengajuan_bimbingan,id_pengajuan'],
            'babak' => ['required', 'string', 'max:50'],
            'tanggal_update' => ['required', 'date'],
            'hasil_akhir' => ['required', 'string', 'max:100'],
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
            'babak.required' => 'Nama tahapan/babak kompetisi wajib diisi.',
            'babak.max' => 'Nama babak maksimal 50 karakter.',
            'tanggal_update.required' => 'Tanggal capaian/update wajib diisi.',
            'tanggal_update.date' => 'Format tanggal tidak valid.',
            'hasil_akhir.required' => 'Status hasil babak wajib diisi.',
            'hasil_akhir.max' => 'Status hasil babak maksimal 100 karakter.',
        ];
    }
}
