<?php

namespace App\Enums;

enum BookRentalStatus: string
{
    case ACTIVE = 'active';
    case RETURNED = 'returned';
    case OVERDUE = 'overdue';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
