<?php

namespace App\Http\Requests\Auth;

use App\Enums\Prodi;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public const ANGKATAN_MIN = 2022;

    public const ANGKATAN_MAX = 2026;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = UserRole::tryFrom((string) $this->input('role')) ?? UserRole::Mahasiswa;
        $isMahasiswa = $role === UserRole::Mahasiswa;

        return [
            'role' => ['required', Rule::in(UserRole::registrable())],
            'name' => ['required', 'string', 'max:255'],
            'nim_nip' => [
                'required',
                // Mahasiswa: NIM 8–15 digit. Dosen: NIP tepat 18 digit.
                $isMahasiswa ? 'digits_between:8,15' : 'digits:18',
                'unique:users,nim_nip',
            ],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                'unique:users,email',
                // Mahasiswa wajib email biasa (bukan email kampus). Dosen bebas memakai email apa saja.
                function (string $attribute, mixed $value, \Closure $fail) use ($isMahasiswa) {
                    $isCampus = (bool) preg_match('/@([a-z0-9-]+\.)*polinema\.ac\.id$/i', (string) $value);

                    if ($isMahasiswa && $isCampus) {
                        $fail('Mahasiswa wajib menggunakan email biasa (bukan email kampus).');
                    }
                },
            ],
            // Prodi & angkatan hanya diwajibkan untuk mahasiswa.
            'prodi' => [Rule::requiredIf($isMahasiswa), 'nullable', Rule::in(Prodi::options())],
            'angkatan' => [
                Rule::requiredIf($isMahasiswa),
                'nullable',
                'integer',
                'between:'.self::ANGKATAN_MIN.','.self::ANGKATAN_MAX,
            ],
            // Min. 6 karakter, mengandung huruf dan karakter khusus. 'bail' = tampilkan 1 pesan saja.
            'password' => [
                'bail',
                'required',
                'string',
                'min:6',
                fn (string $attribute, mixed $value, \Closure $fail) => preg_match('/\pL/u', (string) $value)
                    ? null
                    : $fail('Kata sandi harus mengandung huruf.'),
                fn (string $attribute, mixed $value, \Closure $fail) => preg_match('/[^\pL\pN\s]/u', (string) $value)
                    ? null
                    : $fail('Kata sandi harus mengandung karakter khusus (contoh: ! @ # $ %).'),
                'confirmed',
            ],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        $isDosen = $this->input('role') === UserRole::Dosen->value;

        return [
            'role.required' => 'Pilih peran akun terlebih dahulu.',
            'role.in' => 'Peran akun tidak valid.',
            'name.required' => $isDosen ? 'Nama lengkap wajib diisi.' : 'Nama mahasiswa wajib diisi.',
            'nim_nip.required' => $isDosen ? 'NIP wajib diisi.' : 'NIM wajib diisi.',
            'nim_nip.digits' => 'NIP harus berupa 18 digit angka.',
            'nim_nip.digits_between' => 'NIM harus berupa angka (8–15 digit).',
            'nim_nip.unique' => 'Nomor induk ini sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'prodi.required' => 'Program studi wajib dipilih.',
            'prodi.in' => 'Program studi tidak valid. Pilih salah satu dari daftar.',
            'angkatan.required' => 'Tahun angkatan wajib dipilih.',
            'angkatan.integer' => 'Tahun angkatan tidak valid.',
            'angkatan.between' => 'Tahun angkatan harus antara '.self::ANGKATAN_MIN.' dan '.self::ANGKATAN_MAX.'.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
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