<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SSOTest extends TestCase
{
    use RefreshDatabase;

    public function test_sso_portal_page_renders_successfully(): void
    {
        $response = $this->get(route('sso.login'));

        $response->assertOk()
            ->assertSee('POLITEKNIK NEGERI MALANG')
            ->assertSee('Single Sign-On (SSO)')
            ->assertSee('Kata Sandi SIAKAD');
    }

    public function test_mahasiswa_can_login_via_sso_with_valid_siakad_credentials(): void
    {
        $mahasiswa = Mahasiswa::create([
            'nim' => '244107060116',
            'nama' => 'Sastra Maheva Zaky',
            'email_kampus' => 'sastra.maheva@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $response = $this->post(route('sso.authenticate'), [
            'nim' => '244107060116',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertTrue(session()->has('auth_user'));
        $this->assertEquals('244107060116', session('auth_user')['id']);
        $this->assertEquals('Sastra Maheva Zaky', session('auth_user')['nama']);
    }

    public function test_sso_fails_when_nim_not_registered(): void
    {
        $response = $this->from(route('sso.login'))->post(route('sso.authenticate'), [
            'nim' => '999999999999',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('sso.login'))
            ->assertSessionHasErrors('nim');

        $this->assertGuest();
        $errors = session('errors')->get('nim');
        $this->assertStringContainsString('tidak ditemukan dalam pangkalan data SIAKAD', $errors[0]);
    }

    public function test_sso_fails_when_password_is_incorrect(): void
    {
        Mahasiswa::create([
            'nim' => '244107060116',
            'nama' => 'Sastra Maheva Zaky',
            'email_kampus' => 'sastra.maheva@student.polinema.ac.id',
            'password' => Hash::make('password123'),
            'prodi' => 'D-IV Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $response = $this->from(route('sso.login'))->post(route('sso.authenticate'), [
            'nim' => '244107060116',
            'password' => 'passwordsalah',
        ]);

        $response->assertRedirect(route('sso.login'))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
        $errors = session('errors')->get('password');
        $this->assertStringContainsString('Kata sandi SIAKAD yang Anda masukkan salah', $errors[0]);
    }
}
