<?php

namespace App\Enums;

enum Prodi: string
{
    case D4SistemInformasiBisnis = 'D-IV Sistem Informasi Bisnis';
    case D4TeknikInformatika = 'D-IV Teknik Informatika';
    case D3ManajemenInformatika = 'D-III Manajemen Informatika';
    case D3TeknikInformatika = 'D-III Teknik Informatika';

    /**
     * Daftar nilai (sama dengan label) untuk pilihan dropdown dan validasi.
     *
     * @return list<string>
     */
    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}