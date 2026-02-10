<?php

namespace App\Enums;

enum RelationType: string
{
    case Owner = 'owner';
    case Tenant = 'tenant';

    public function label(): string
    {
        return match($this) {
            self::Owner => 'Malik',
            self::Tenant => 'Kiracı',
        };
    }
}
