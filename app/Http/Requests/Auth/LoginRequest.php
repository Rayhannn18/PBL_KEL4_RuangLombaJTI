<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Email, NIM, atau NIP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Kolom yang dipakai untuk mencari akun: email jika berformat email, selain itu NIM/NIP.
     */
    public function credentialField(): string
    {
        return filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'nim_nip';
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['login' => strtolower(trim((string) $this->input('login')))]);
    }
}