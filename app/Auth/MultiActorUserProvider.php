<?php

namespace App\Auth;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class MultiActorUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $sessionUser = session('auth_user');
        if ($sessionUser && ($sessionUser['id'] ?? null) == $identifier) {
            return $this->toGenericUser($sessionUser);
        }

        $mhs = Mahasiswa::where('nim', (string) $identifier)->first();
        if ($mhs) {
            return $this->userFromMahasiswa($mhs);
        }

        $dsn = Dosen::where('nidn', (string) $identifier)->first();
        if ($dsn) {
            return $this->userFromDosen($dsn);
        }

        $adm = Admin::where('id_admin', $identifier)->first();
        if ($adm) {
            return $this->userFromAdmin($adm);
        }

        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token) {}

    /**
     * Retrieve a user by the given credentials.
     *
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        if (empty($credentials)) {
            return null;
        }

        $roleInput = $credentials['role'] ?? null;
        $login = $credentials['email'] ?? $credentials['nim_nip'] ?? $credentials['login'] ?? null;

        if (! $login) {
            return null;
        }

        $login = trim((string) $login);
        $role = $roleInput instanceof UserRole ? $roleInput->value : (string) $roleInput;

        if ($role === 'mahasiswa') {
            $mhs = Mahasiswa::where('nim', $login)
                ->orWhereRaw('LOWER(email_kampus) = ?', [strtolower($login)])
                ->first();

            return $mhs ? $this->userFromMahasiswa($mhs) : null;
        }

        if ($role === 'dosen') {
            $dsn = Dosen::where('nidn', $login)
                ->orWhereRaw('LOWER(email_kampus) = ?', [strtolower($login)])
                ->first();

            return $dsn ? $this->userFromDosen($dsn) : null;
        }

        if ($role === 'admin') {
            $adm = Admin::whereRaw('LOWER(email) = ?', [strtolower($login)])->first();

            return $adm ? $this->userFromAdmin($adm) : null;
        }

        // Jika role tidak spesifik, cari berturut-turut pada tabel admin, dosen, mahasiswa
        $adm = Admin::whereRaw('LOWER(email) = ?', [strtolower($login)])->first();
        if ($adm) {
            return $this->userFromAdmin($adm);
        }

        $dsn = Dosen::whereRaw('LOWER(email_kampus) = ?', [strtolower($login)])->orWhere('nidn', $login)->first();
        if ($dsn) {
            return $this->userFromDosen($dsn);
        }

        $mhs = Mahasiswa::whereRaw('LOWER(email_kampus) = ?', [strtolower($login)])->orWhere('nim', $login)->first();
        if ($mhs) {
            return $this->userFromMahasiswa($mhs);
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials)
    {
        $plain = $credentials['password'] ?? '';

        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required and supported.
     *
     * @return void
     */
    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false) {}

    private function userFromMahasiswa(Mahasiswa $mhs): User
    {
        $user = new User([
            'name' => $mhs->nama,
            'email' => $mhs->email_kampus,
            'role' => UserRole::Mahasiswa,
            'nim_nip' => $mhs->nim,
            'prodi' => $mhs->prodi,
            'angkatan' => $mhs->angkatan,
            'password' => $mhs->password,
        ]);
        $user->id = $mhs->nim;
        $user->exists = true;

        return $user;
    }

    private function userFromDosen(Dosen $dsn): User
    {
        $user = new User([
            'name' => $dsn->nama,
            'email' => $dsn->email_kampus,
            'role' => UserRole::Dosen,
            'nim_nip' => $dsn->nidn,
            'prodi' => null,
            'angkatan' => null,
            'password' => $dsn->password,
        ]);
        $user->id = $dsn->nidn;
        $user->exists = true;

        return $user;
    }

    private function userFromAdmin(Admin $adm): User
    {
        $user = new User([
            'name' => $adm->nama,
            'email' => $adm->email,
            'role' => UserRole::Admin,
            'nim_nip' => null,
            'prodi' => null,
            'angkatan' => null,
            'password' => $adm->password,
        ]);
        $user->id = (string) $adm->id_admin;
        $user->exists = true;

        return $user;
    }

    private function toGenericUser(array $sessionUser): User
    {
        $roleValue = $sessionUser['role'] ?? 'mahasiswa';
        $role = UserRole::tryFrom($roleValue) ?? UserRole::Mahasiswa;

        $user = new User([
            'name' => $sessionUser['nama'] ?? '',
            'email' => $sessionUser['email'] ?? '',
            'role' => $role,
            'nim_nip' => $role === UserRole::Admin ? null : ($sessionUser['id'] ?? null),
            'prodi' => $sessionUser['prodi'] ?? null,
            'angkatan' => $sessionUser['angkatan'] ?? null,
        ]);
        $user->id = $sessionUser['id'] ?? null;
        $user->exists = true;

        return $user;
    }
}
