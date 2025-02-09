<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    protected $fillable = [
        'title', 
        'published', 
        'isbn', 
        'isbn13'
    ];

    public function copies()
    {
        return $this->hasMany(BookCopy::class);
    }
}
