<?php

namespace App\Enums;

enum BookCopyStatus: string
{
    case AVAILABLE = 'available';
    case RENTED = 'rented';
    case LOST = 'lost';
}