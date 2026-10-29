<?php

namespace App\Enums;

enum ModePelaksanaan: string
{
    case Online = 'Online';
    case Offline = 'Offline';
    case Hybrid = 'Hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online (Daring)',
            self::Offline => 'Offline (Luring)',
            self::Hybrid => 'Hybrid',
        };
    }

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}
