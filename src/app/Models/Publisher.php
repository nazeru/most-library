<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'isni',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}
