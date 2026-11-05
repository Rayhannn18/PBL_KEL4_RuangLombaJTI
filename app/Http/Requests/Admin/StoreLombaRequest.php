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

        if (! $this->hasFile('poster') && $this->hasFile('pamflet')) {
            $this->files->set('poster', $this->file('pamflet'));
        }

        if (! $this->hasFile('buku_pedoman') && $this->hasFile('guidebook')) {
            $this->files->set('buku_pedoman', $this->file('guidebook'));
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
            'poster' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'buku_pedoman' => ['required', 'file', 'mimes:pdf', 'max:10240'],
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
            'poster.required' => 'Pamflet lomba wajib diunggah.',
            'poster.image' => 'Pamflet lomba harus berupa file gambar.',
            'poster.mimes' => 'Format file pamflet harus berupa JPG, JPEG, atau PNG.',
            'poster.max' => 'Ukuran file pamflet maksimal 2 MB.',
            'buku_pedoman.required' => 'Buku panduan (guidebook) wajib diunggah.',
            'buku_pedoman.file' => 'Buku panduan harus berupa file dokumen yang valid.',
            'buku_pedoman.mimes' => 'Format file buku panduan harus berupa PDF.',
            'buku_pedoman.max' => 'Ukuran file buku panduan maksimal 10 MB.',
        ];
    }
}
