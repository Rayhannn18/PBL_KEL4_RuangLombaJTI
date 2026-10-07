<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test Login Page loads
     */
    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Portal Masuk Sistem');
        $response->assertSee('Mahasiswa');
        $response->assertSee('Dosen');
        $response->assertSee('Admin');
    }

    /**
     * Test Register Page loads
     */
    public function test_register_page_loads_successfully(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Akun');
        $response->assertSee('Mahasiswa');
        $response->assertSee('D-IV Sistem Informasi Bisnis');
    }

    /**
     * Test Mahasiswa login with valid credentials
     */
    public function test_mahasiswa_can_login_with_valid_credentials(): void
    {
        $mahasiswa = Mahasiswa::first();

        $response = $this->post('/login', [
            'role' => 'mahasiswa',
            'identifier' => $mahasiswa->nim,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.analitik'));
        $this->assertTrue(session()->has('auth_user'));
        $this->assertEquals($mahasiswa->nama, session('auth_user')['nama']);
    }

    /**
     * Test login fails with invalid credentials
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'role' => 'mahasiswa',
            'identifier' => 'wrongnim',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
        $this->assertFalse(session()->has('auth_user'));
    }

    /**
     * Test Mahasiswa registration
     */
    public function test_mahasiswa_can_register(): void
    {
        $payload = [
            'nim' => '244107069999',
            'nama' => 'Budi Santoso',
            'email_kampus' => 'budi.santoso@student.polinema.ac.id',
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $payload);

        $response->assertRedirect(route('dashboard.analitik'));
        $this->assertDatabaseHas('mahasiswa', [
            'nim' => '244107069999',
            'nama' => 'Budi Santoso',
        ]);
        $this->assertTrue(session()->has('auth_user'));
    }

    /**
     * Test Quick Login demo feature
     */
    public function test_quick_login_works(): void
    {
        $response = $this->get('/quick-login/dosen');

        $response->assertRedirect(route('dashboard.analitik'));
        $this->assertTrue(session()->has('auth_user'));
        $this->assertEquals('dosen', session('auth_user')['role']);
    }

    /**
     * Test logout functionality
     */
    public function test_user_can_logout(): void
    {
        $this->get('/quick-login/mahasiswa');
        $this->assertTrue(session()->has('auth_user'));

        $response = $this->post('/logout');

        $response->assertRedirect(route('dashboard.analitik'));
        $this->assertFalse(session()->has('auth_user'));
    }
}
