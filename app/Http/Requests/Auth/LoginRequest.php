<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(UserRole::class)],
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Pilih peran terlebih dahulu.',
            'role.enum' => 'Peran akun tidak valid.',
            'login.required' => $this->input('role') === UserRole::Admin->value
                ? 'Email wajib diisi.'
                : 'Email, NIM, atau NIP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    public function role(): UserRole
    {
        return UserRole::from($this->validated('role'));
    }

    /**
     * Kolom pencarian akun: email jika berformat email, selain itu NIM (mahasiswa) / NIP (dosen).
     * Admin hanya boleh masuk dengan email.
     */
    public function credentialField(): string
    {
        return filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'nim_nip';
    }

    protected function prepareForValidation(): void
    {
        $login = $this->input('login') ?? $this->input('identifier') ?? $this->input('email');
        $this->merge(['login' => strtolower(trim((string) $login))]);
    }
}
