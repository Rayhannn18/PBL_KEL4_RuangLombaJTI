<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = UserRole::tryFrom((string) $this->input('role')) ?? UserRole::Mahasiswa;
        $domain = $role->emailDomain();

        return [
            'role' => ['required', Rule::enum(UserRole::class)],
            'name' => ['required', 'string', 'max:255'],
            'nim_nip' => [
                'required',
                $role === UserRole::Mahasiswa ? 'digits_between:8,15' : 'digits_between:10,18',
                'unique:users,nim_nip',
            ],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                'unique:users,email',
                // Cocokkan persis domain; "@polinema.ac.id" tidak boleh lolos sebagai "@student.polinema.ac.id".
                fn (string $attribute, mixed $value, \Closure $fail) => str_ends_with((string) $value, '@'.$domain)
                    ? null
                    : $fail("Gunakan email kampus dengan domain @{$domain}."),
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Pilih peran akun terlebih dahulu.',
            'role.enum' => 'Peran akun tidak valid.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'nim_nip.required' => $this->input('role') === UserRole::Dosen->value
                ? 'NIP / NIDN wajib diisi.'
                : 'NIM wajib diisi.',
            'nim_nip.digits_between' => $this->input('role') === UserRole::Dosen->value
                ? 'NIP / NIDN harus berupa angka (10–18 digit).'
                : 'NIM harus berupa angka (8–15 digit).',
            'nim_nip.unique' => 'Nomor induk ini sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan serta Kebijakan Privasi.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'nim_nip' => trim((string) $this->input('nim_nip')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }
}