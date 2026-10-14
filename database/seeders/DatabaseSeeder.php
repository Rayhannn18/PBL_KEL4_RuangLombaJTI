<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Muhammad Farhan',
            'email' => 'farhan.muhammad@gmail.com',
            'nim_nip' => '2241720088',
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => 2022,
        ]);

        User::factory()->dosen()->create([
            'name' => 'Dr. Eng. Rosa Andrie Asmara, S.T., M.T.',
            'email' => 'rosa.andrie@polinema.ac.id',
            'nim_nip' => '198010102005012001',
        ]);

        // Akun admin untuk pengembangan. GANTI kata sandinya sebelum dipakai di server sungguhan.
        User::factory()->admin()->create([
            'name' => 'Admin RuangLombaJTI',
            'email' => 'admin@ruanglombajti.test',
        ]);
    }
}