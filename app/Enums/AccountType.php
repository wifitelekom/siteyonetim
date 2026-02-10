<?php

namespace App\Enums;

enum AccountType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Asset = 'asset';
    case Liability = 'liability';

    public function label(): string
    {
        return match($this) {
            self::Income => 'Gelir',
            self::Expense => 'Gider',
            self::Asset => 'Varlık',
            self::Liability => 'Borç',
        };
    }


    public function color(): string
    {
        return match($this) {
            self::Income => 'success',
            self::Expense => 'danger',
            self::Asset => 'primary',
            self::Liability => 'warning',
        };
    }
}
