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
            'email' => '2241720088@student.polinema.ac.id',
            'nim_nip' => '2241720088',
        ]);

        User::factory()->dosen()->create([
            'name' => 'Dr. Eng. Rosa Andrie Asmara, S.T., M.T.',
            'email' => 'rosa.andrie@polinema.ac.id',
            'nim_nip' => '198010102005012001',
        ]);
    }
}
