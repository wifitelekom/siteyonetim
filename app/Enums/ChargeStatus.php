<?php

namespace App\Enums;

enum ChargeStatus: string
{
    case Open = 'open';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match($this) {
            self::Open => 'Açık',
            self::Paid => 'Ödendi',
            self::Overdue => 'Gecikmiş',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Open => 'warning',
            self::Paid => 'success',
            self::Overdue => 'danger',
        };
    }
}
