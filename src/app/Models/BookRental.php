<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookRental extends Model
{

    protected $table = 'books_rentals';

    protected $fillable = [
        'user_id', 'book_copy_id', 'rental_date', 'due_date', 'return_date', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    

    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class);
    }
}
