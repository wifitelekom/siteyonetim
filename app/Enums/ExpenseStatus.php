<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    public function label(): string
    {
        return match($this) {
            self::Unpaid => 'Ödenmedi',
            self::Partial => 'Kısmi Ödendi',
            self::Paid => 'Ödendi',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Unpaid => 'secondary',
            self::Partial => 'info',
            self::Paid => 'success',
        };
    }
}
