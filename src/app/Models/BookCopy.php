<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\BookCopyStatus;

class BookCopy extends Model
{
    use HasFactory;

    protected $table = 'books_copies';

    protected $fillable = [
        'book_id',
        'barcode', 
        'status', 
    ];

    protected $casts = [
        'status' => BookCopyStatus::class,
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', BookCopyStatus::AVAILABLE);
    }
}
