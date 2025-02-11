<?php

namespace App\Enums;

enum BookCopyStatus: string
{
    case AVAILABLE = 'available';
    case RENTED = 'rented';
    case LOST = 'lost';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}