<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function mahasiswaPayload(array $overrides = []): array
    {
        return array_merge([
            'role' => 'mahasiswa',
            'name' => 'Muhammad Farhan',
            'nim_nip' => '2241720088',
            'email' => 'farhan.muhammad@gmail.com',
            'prodi' => 'D-IV Teknik Informatika',
            'angkatan' => '2022',
            'password' => 'Rahasia@123',
            'password_confirmation' => 'Rahasia@123',
            'terms' => '1',
        ], $overrides);
    }

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke Akun');
        $this->get('/register')->assertOk()->assertSee('Buat Akun Baru');
    }

    public function test_mahasiswa_can_register(): void
    {
        $this->post('/register', $this->mahasiswaPayload())->assertRedirect('/dashboard');

        $user = User::where('email', 'farhan.muhammad@gmail.com')->firstOrFail();
        $this->assertSame(UserRole::Mahasiswa, $user->role);
        $this->assertSame('2241720088', $user->nim_nip);
        $this->assertSame('D-IV Teknik Informatika', $user->prodi);
        $this->assertSame(2022, $user->angkatan);
        $this->assertAuthenticatedAs($user);
    }

    public function test_dosen_can_register(): void
    {
        $this->post('/register', $this->mahasiswaPayload([
            'role' => 'dosen',
            'name' => 'Dr. Eng. Rosa Andrie Asmara, S.T., M.T.',
            'nim_nip' => '198010102005012001',
            'email' => 'rosa.andrie@polinema.ac.id',
        ]))->assertRedirect('/dashboard');

        $dosen = User::firstOrFail();
        $this->assertSame(UserRole::Dosen, $dosen->role);
        $this->assertNull($dosen->prodi);
        $this->assertNull($dosen->angkatan);
    }

    public function test_mahasiswa_must_use_non_campus_email(): void
    {
        $this->post('/register', $this->mahasiswaPayload(['email' => '2241720088@student.polinema.ac.id']))
            ->assertSessionHasErrors('email');
        $this->post('/register', $this->mahasiswaPayload(['email' => 'budi@polinema.ac.id']))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);

        $this->post('/register', $this->mahasiswaPayload(['email' => 'budi.santoso@gmail.com']))
            ->assertRedirect('/dashboard');
    }

    public function test_dosen_can_use_any_email(): void
    {
        $this->post('/register', $this->mahasiswaPayload([
            'role' => 'dosen',
            'name' => 'Dr. Andi Wijaya, S.T., M.T.',
            'nim_nip' => '198010102005012001',
            'email' => 'andi.wijaya@gmail.com',
        ]))->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['email' => 'andi.wijaya@gmail.com', 'role' => 'dosen']);
    }

    public function test_dosen_nip_must_be_exactly_18_digits(): void
    {
        $dosen = fn (string $nip) => $this->mahasiswaPayload([
            'role' => 'dosen', 'nim_nip' => $nip, 'email' => 'andi.wijaya@gmail.com',
        ]);

        $this->post('/register', $dosen('12345'))->assertSessionHasErrors('nim_nip');
        $this->post('/register', $dosen('1980101020050120011'))->assertSessionHasErrors('nim_nip');  // 19 digit
        $this->post('/register', $dosen('19801010200501200A'))->assertSessionHasErrors('nim_nip');   // ada huruf
        $this->assertDatabaseCount('users', 0);

        $this->post('/register', $dosen('198010102005012001'))->assertRedirect('/dashboard');
    }

    public function test_registration_requires_terms_and_matching_password(): void
    {
        $this->post('/register', $this->mahasiswaPayload(['terms' => null]))->assertSessionHasErrors('terms');
        $this->post('/register', $this->mahasiswaPayload(['password_confirmation' => 'Beda@123']))->assertSessionHasErrors('password');
    }

    public function test_password_must_be_6_chars_with_letter_and_symbol(): void
    {
        foreach (['Ab@1', 'abc123', '123456@'] as $password) {
            $this->post('/register', $this->mahasiswaPayload([
                'password' => $password, 'password_confirmation' => $password,
            ]))->assertSessionHasErrors('password');
        }
        $this->assertDatabaseCount('users', 0);

        $this->post('/register', $this->mahasiswaPayload([
            'password' => 'abc@12', 'password_confirmation' => 'abc@12',
        ]))->assertRedirect('/dashboard');
    }

    public function test_mahasiswa_requires_valid_prodi_and_angkatan(): void
    {
        $this->post('/register', $this->mahasiswaPayload(['prodi' => null]))->assertSessionHasErrors('prodi');
        $this->post('/register', $this->mahasiswaPayload(['prodi' => 'S2 Informatika']))->assertSessionHasErrors('prodi');
        $this->post('/register', $this->mahasiswaPayload(['angkatan' => null]))->assertSessionHasErrors('angkatan');
        $this->post('/register', $this->mahasiswaPayload(['angkatan' => '2021']))->assertSessionHasErrors('angkatan');
        $this->post('/register', $this->mahasiswaPayload(['angkatan' => '2027']))->assertSessionHasErrors('angkatan');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_all_four_prodi_and_angkatan_range_are_accepted(): void
    {
        $prodi = ['D-IV Sistem Informasi Bisnis', 'D-IV Teknik Informatika', 'D-III Manajemen Informatika', 'D-III Teknik Informatika'];

        foreach ($prodi as $i => $nama) {
            $this->post('/register', $this->mahasiswaPayload([
                'nim_nip' => '22417200'.(10 + $i),
                'email' => "mhs{$i}@gmail.com",
                'prodi' => $nama,
                'angkatan' => (string) (2022 + $i),
            ]))->assertRedirect('/dashboard');
            auth()->logout();
        }

        $this->assertDatabaseCount('users', 4);
    }

    public function test_duplicate_nim_or_email_is_rejected(): void
    {
        $this->post('/register', $this->mahasiswaPayload());
        auth()->logout();

        $this->post('/register', $this->mahasiswaPayload())->assertSessionHasErrors(['email', 'nim_nip']);
    }

    public function test_admin_cannot_self_register(): void
    {
        $this->post('/register', $this->mahasiswaPayload(['role' => 'admin']))
            ->assertSessionHasErrors('role');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'farhan.muhammad@gmail.com']);
    }

    public function test_user_can_login_with_email_or_nim(): void
    {
        $user = User::factory()->create([
            'email' => 'farhan.muhammad@gmail.com',
            'nim_nip' => '2241720088',
            'password' => 'rahasia123',
        ]);

        $this->post('/login', ['role' => 'mahasiswa', 'login' => 'farhan.muhammad@gmail.com', 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', ['role' => 'mahasiswa', 'login' => '2241720088', 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_page_shows_three_roles(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('value="mahasiswa"', false)
            ->assertSee('value="dosen"', false)
            ->assertSee('value="admin"', false);
    }

    public function test_login_requires_role(): void
    {
        User::factory()->create(['nim_nip' => '2241720088', 'password' => 'rahasia123']);

        $this->post('/login', ['login' => '2241720088', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_dosen_can_login_with_nip_only_as_dosen(): void
    {
        $dosen = User::factory()->dosen()->create(['nim_nip' => '198010102005012001', 'password' => 'rahasia123']);

        $this->post('/login', ['role' => 'dosen', 'login' => '198010102005012001', 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($dosen);
    }

    public function test_wrong_role_cannot_login(): void
    {
        User::factory()->dosen()->create(['nim_nip' => '198010102005012001', 'password' => 'rahasia123']);

        $this->post('/login', ['role' => 'mahasiswa', 'login' => '198010102005012001', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('login');
        $this->post('/login', ['role' => 'admin', 'login' => '198010102005012001', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_logs_in_with_email_only(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@ruanglombajti.test', 'password' => 'rahasia123']);

        $this->post('/login', ['role' => 'admin', 'login' => 'admin@ruanglombajti.test', 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
        $this->post('/logout');

        // Admin tidak punya NIM/NIP, jadi login selain email ditolak.
        $this->post('/login', ['role' => 'admin', 'login' => '12345678', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();

        // Akun admin tidak bisa masuk lewat pilihan Mahasiswa.
        $this->post('/login', ['role' => 'mahasiswa', 'login' => 'admin@ruanglombajti.test', 'password' => 'rahasia123'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['nim_nip' => '2241720088', 'password' => 'rahasia123']);

        $this->post('/login', ['role' => 'mahasiswa', 'login' => '2241720088', 'password' => 'salah'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk();
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard');
    }
}