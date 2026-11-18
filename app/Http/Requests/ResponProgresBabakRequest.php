<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResponProgresBabakRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Otorisasi dosen pembimbing dilakukan di controller.
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
            'alasan_penolakan' => ['nullable', 'required_if:status_setuju,ditolak', 'string', 'min:10', 'max:500'],
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
            'alasan_penolakan.required_if' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.string' => 'Alasan penolakan harus berupa teks.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 10 karakter.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
