<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Book;
use App\Models\Author;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Book::factory()->count(10)->create();

        $authors = Author::all();

        Book::all()->each(function ($book) use ($authors){
            $book->authors()->attach(
                $authors->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
