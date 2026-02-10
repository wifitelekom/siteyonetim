<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case Online = 'online';

    public function label(): string
    {
        return match($this) {
            self::Cash => 'Nakit',
            self::Bank => 'Banka',
            self::Online => 'Online',
        };
    }
}
