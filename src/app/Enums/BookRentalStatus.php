<?php

namespace App\Enums;

enum BookRentalStatus: string
{
    case ACTIVE = 'active';
    case RETURNED = 'returned';
    case OVERDUE = 'overdue';
    case RETURNED_WITH_OVERDUE = 'returned_with_overdue';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
