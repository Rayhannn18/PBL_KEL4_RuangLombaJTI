<?php

namespace App\Enums;

enum TipePeserta: string
{
    case Individu = 'Individu';
    case Kelompok = 'Kelompok';

    public function label(): string
    {
        return $this->value;
    }

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}
