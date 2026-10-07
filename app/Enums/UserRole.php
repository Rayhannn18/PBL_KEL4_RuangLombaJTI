<?php

namespace App\Enums;

enum UserRole: string
{
    case Mahasiswa = 'mahasiswa';
    case Dosen = 'dosen';

    public function label(): string
    {
        return match ($this) {
            self::Mahasiswa => 'Mahasiswa',
            self::Dosen => 'Dosen',
        };
    }

    /**
     * Domain email yang wajib dipakai peran ini; null berarti email apa saja boleh.
     */
    public function requiredEmailDomain(): ?string
    {
        return match ($this) {
            self::Mahasiswa => null,
            self::Dosen => 'polinema.ac.id',
        };
    }
}