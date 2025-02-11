<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function reader_can_view_available_books()
    {
        $reader = User::factory()->create(['role' => 'reader']);

        $availableBook = Book::factory()->create();
        BookCopy::factory()->create(['book_id' => $availableBook->id, 'status' => 'available']);

        $unavailableBook = Book::factory()->create();
        BookCopy::factory()->create(['book_id' => $unavailableBook->id, 'status' => 'rented']);

        $this->actingAs($reader);
        
        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['title' => $availableBook->title])
                 ->assertJsonMissing(['title' => $unavailableBook->title]);
    }

    #[Test]
    public function librarian_can_view_all_books()
    {
        $librarian = User::factory()->create(['role' => 'librarian']);

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $this->actingAs($librarian);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment(['title' => $book1->title])
                 ->assertJsonFragment(['title' => $book2->title]);
    }
}