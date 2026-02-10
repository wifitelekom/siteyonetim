<?php

namespace App\Enums;

enum ChargeType: string
{
    case Aidat = 'aidat';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Aidat => 'Aidat',
            self::Other => 'Diğer',
        };
    }
}
