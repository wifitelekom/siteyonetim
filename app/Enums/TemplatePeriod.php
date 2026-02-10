<?php

namespace App\Enums;

enum TemplatePeriod: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match($this) {
            self::Monthly => 'Aylık',
            self::Quarterly => '3 Aylık',
            self::Yearly => 'Yıllık',
        };
    }

    public function months(): int
    {
        return match($this) {
            self::Monthly => 1,
            self::Quarterly => 3,
            self::Yearly => 12,
        };
    }
}
