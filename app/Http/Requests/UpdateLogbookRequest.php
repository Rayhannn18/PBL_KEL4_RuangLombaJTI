<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLogbookRequest extends FormRequest
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
            'tanggal' => ['required', 'date'],
            'materi' => ['required', 'string', 'max:1000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:1000'],
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
            'tanggal.required' => 'Tanggal pelaksanaan bimbingan wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'materi.required' => 'Materi bahasan konsultasi wajib diisi.',
            'materi.max' => 'Materi bahasan maksimal 1000 karakter.',
            'tindak_lanjut.max' => 'Rencana tindak lanjut maksimal 1000 karakter.',
        ];
    }
}
