<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResponProgresBabakRequest extends FormRequest
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
            'status_setuju' => ['required', 'in:disetujui,ditolak'],
            'catatan_dosen' => ['nullable', 'string', 'max:1000', 'required_if:status_setuju,ditolak'],
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
            'status_setuju.required' => 'Status keputusan progres babak wajib dipilih.',
            'status_setuju.in' => 'Status keputusan harus berupa disetujui atau ditolak.',
            'catatan_dosen.required_if' => 'Catatan arahan/alasan revisi wajib diisi apabila menolak progres babak.',
            'catatan_dosen.max' => 'Catatan dosen maksimal 1000 karakter.',
        ];
    }
}
