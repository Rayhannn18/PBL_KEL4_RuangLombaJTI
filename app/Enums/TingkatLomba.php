<?php

namespace App\Enums;

enum TingkatLomba: string
{
    case Internasional = 'Internasional';
    case Nasional = 'Nasional';
    case Kampus = 'Kampus';

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
