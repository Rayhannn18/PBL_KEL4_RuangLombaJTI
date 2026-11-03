<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ValidasiLogbookRequest extends FormRequest
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
            'status_validasi' => ['required', 'in:disetujui,revisi'],
            'catatan_dosen' => ['nullable', 'string', 'max:1000', 'required_if:status_validasi,revisi'],
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
            'status_validasi.required' => 'Status validasi wajib dipilih.',
            'status_validasi.in' => 'Status validasi harus berupa disetujui atau revisi.',
            'catatan_dosen.required_if' => 'Catatan dosen wajib diisi apabila meminta revisi logbook.',
            'catatan_dosen.max' => 'Catatan dosen tidak boleh melebihi 1000 karakter.',
        ];
    }
}
