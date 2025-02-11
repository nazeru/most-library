<?php

namespace Tests\Feature;

use App\Enums\BookCopyStatus;
use App\Enums\UserRole;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function reader_can_view_available_books()
    {
        $reader = User::factory()->create(['role' => UserRole::READER]);

        $availableBook = Book::factory()->create();
        BookCopy::factory()->create(['book_id' => $availableBook->id, 'status' => BookCopyStatus::AVAILABLE]);

        $unavailableBook = Book::factory()->create();
        BookCopy::factory()->create(['book_id' => $unavailableBook->id, 'status' => BookCopyStatus::RENTED]);

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
        $librarian = User::factory()->create(['role' => UserRole::LIBRARIAN]);

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $this->actingAs($librarian);
        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment(['title' => $book1->title])
                 ->assertJsonFragment(['title' => $book2->title]);
    }

    #[Test]
    public function librarian_can_add_a_new_book()
    {
        $librarian = User::factory()->create(['role' => UserRole::LIBRARIAN]);

        $bookData = [
            'title' => 'New Book',
            'isbn' => '123456789X',
            'isbn13' => '9781234567897',
            'published' => '2023-01-01',
        ];

        $this->actingAs($librarian);
        $response = $this->postJson('/api/v1/books', $bookData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'New Book']);

        $this->assertDatabaseHas('books', ['title' => 'New Book']);
    }

    #[Test]
    public function reader_cannot_add_a_new_book()
    {
        $reader = User::factory()->create(['role' => UserRole::READER]);

        $bookData = [
            'title' => 'Unauthorized Book',
            'isbn13' => '9781234567897',
        ];

        $this->actingAs($reader);
        $response = $this->postJson('/api/v1/books', $bookData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('books', ['title' => 'Unauthorized Book']);
    }

    #[Test]
    public function librarian_can_update_a_book()
    {
        $librarian = User::factory()->create(['role' => UserRole::LIBRARIAN]);
        $book = Book::factory()->create(['title' => 'Old Title']);

        $this->actingAs($librarian);
        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('books', ['title' => 'Updated Title']);
    }

    #[Test]
    public function reader_cannot_update_a_book()
    {
        $reader = User::factory()->create(['role' => UserRole::READER]);
        $book = Book::factory()->create(['title' => 'Immutable Title']);

        $this->actingAs($reader);
        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('books', ['title' => 'Hacked Title']);
    }

    #[Test]
    public function librarian_can_delete_a_book()
    {
        $librarian = User::factory()->create(['role' => UserRole::LIBRARIAN]);
        $book = Book::factory()->create();

        $this->actingAs($librarian);
        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Book deleted successfully']);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    #[Test]
    public function reader_cannot_delete_a_book()
    {
        $reader = User::factory()->create(['role' => UserRole::READER]);
        $book = Book::factory()->create();

        $this->actingAs($reader);
        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    #[Test]
    public function reader_can_view_a_specific_available_book()
    {
        $reader = User::factory()->create(['role' => UserRole::READER]);
        $book = Book::factory()->create();
        BookCopy::factory()->create(['book_id' => $book->id, 'status' => BookCopyStatus::AVAILABLE]);

        $this->actingAs($reader);
        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $book->title]);
    }

    #[Test]
    public function reader_cannot_view_unavailable_book()
    {
        $reader = User::factory()->create(['role' => UserRole::READER]);
        $book = Book::factory()->create();
        BookCopy::factory()->create(['book_id' => $book->id, 'status' => BookCopyStatus::RENTED]);

        $this->actingAs($reader);
        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(403)
                 ->assertJsonFragment(['error' => 'This book is not available']);
    }

    #[Test]
    public function librarian_can_view_any_book()
    {
        $librarian = User::factory()->create(['role' => UserRole::LIBRARIAN]);
        $book = Book::factory()->create();

        $this->actingAs($librarian);
        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $book->title]);
    }
}
