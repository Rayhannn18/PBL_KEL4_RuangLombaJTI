<?php

namespace App\Http\Requests\Admin;

use App\Enums\ModePelaksanaan;
use App\Enums\TingkatLomba;
use App\Enums\TipePeserta;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLombaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return session('auth_user.role') === 'admin' || auth()->user()?->isAdmin();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('biaya')) {
            $raw = preg_replace('/[^0-9]/', '', (string) $this->input('biaya'));
            $this->merge([
                'biaya' => $raw === '' ? 0 : (int) $raw,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_lomba' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'tingkat' => ['required', 'string', Rule::enum(TingkatLomba::class)],
            'tipe_peserta' => ['required', 'string', Rule::enum(TipePeserta::class)],
            'mode_pelaksanaan' => ['required', 'string', Rule::enum(ModePelaksanaan::class)],
            'penyelenggara' => ['required', 'string', 'max:150'],
            'tenggat' => ['required', 'date'],
            'biaya' => ['required', 'integer', 'min:0'],
            'tautan_daftar' => ['required', 'url', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'persyaratan' => ['required', 'string'],
            'benefit' => ['required', 'string'],
            'poster' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'buku_pedoman' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_lomba.required' => 'Nama lomba wajib diisi.',
            'kategori.required' => 'Kategori minat wajib dipilih.',
            'tingkat.required' => 'Tingkat lomba wajib dipilih.',
            'tipe_peserta.required' => 'Tipe peserta wajib dipilih (Individu atau Kelompok).',
            'mode_pelaksanaan.required' => 'Mode pelaksanaan wajib dipilih (Online, Offline, atau Hybrid).',
            'penyelenggara.required' => 'Penyelenggara lomba wajib diisi.',
            'tenggat.required' => 'Tenggat pendaftaran wajib diisi.',
            'biaya.required' => 'Biaya pendaftaran wajib diisi (isi 0 jika gratis).',
            'biaya.min' => 'Biaya pendaftaran tidak boleh bernilai negatif.',
            'tautan_daftar.required' => 'Tautan pendaftaran resmi wajib diisi.',
            'tautan_daftar.url' => 'Format tautan pendaftaran tidak valid.',
            'deskripsi.required' => 'Deskripsi kompetisi wajib diisi.',
            'persyaratan.required' => 'Persyaratan & ketentuan delegasi wajib diisi.',
            'benefit.required' => 'Benefit & penghargaan wajib diisi.',
            'poster.required' => 'Poster lomba wajib diunggah.',
            'poster.image' => 'Poster harus berupa file gambar.',
            'poster.mimes' => 'Format file poster harus berupa PNG, JPG, atau JPEG.',
            'poster.max' => 'Ukuran file poster maksimal 2 MB.',
            'buku_pedoman.required' => 'Buku pedoman wajib diunggah.',
            'buku_pedoman.file' => 'Buku pedoman harus berupa file dokumen yang valid.',
            'buku_pedoman.mimes' => 'Format file buku pedoman harus berupa PDF, DOC, atau DOCX.',
            'buku_pedoman.max' => 'Ukuran file buku pedoman maksimal 10 MB.',
        ];
    }
}
