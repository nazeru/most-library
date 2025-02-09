<?php

namespace App\Enums;

enum UserRole: string
{
    case READER = 'reader'; 
    case LIBRARIAN = 'librarian';
}