<?php

namespace App\Enums;

enum UserRole: string
{
    case READER = 'reader'; 
    case LIBRARIAN = 'librarian';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}