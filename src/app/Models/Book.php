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
        'isbn13',
        'publisher_id',
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function copies()
    {
        return $this->hasMany(BookCopy::class);
    }
}
