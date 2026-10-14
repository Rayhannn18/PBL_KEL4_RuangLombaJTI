<?php

namespace App\Enums;

enum UserRole: string
{
    case Mahasiswa = 'mahasiswa';
    case Dosen = 'dosen';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Mahasiswa => 'Mahasiswa',
            self::Dosen => 'Dosen',
            self::Admin => 'Admin',
        };
    }

    /**
     * Peran yang boleh mendaftar sendiri. Admin dibuat lewat seeder/admin, tidak lewat form.
     *
     * @return list<string>
     */
    public static function registrable(): array
    {
        return [self::Mahasiswa->value, self::Dosen->value];
    }
}