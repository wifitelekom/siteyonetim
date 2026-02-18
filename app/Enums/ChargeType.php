<?php

namespace App\Enums;

enum ChargeType: string
{
    case Aidat = 'aidat';
    case Other = 'other';
    case OpeningBalance = 'opening_balance';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match($this) {
            self::Aidat => 'Aidat',
            self::Other => 'Diger',
            self::OpeningBalance => 'Acilis Bakiyesi',
            self::Transfer => 'Borc Aktarma',
        };
    }
}
