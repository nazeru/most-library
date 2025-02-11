<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use App\Models\BookCopy;
use App\Enums\BookCopyStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class BookCopyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateLibrarian()
    {
        $librarian = User::create([
            'name' => 'Test Librarian',
            'email' => 'librarian_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LIBRARIAN,
        ]);

        $token = JWTAuth::fromUser($librarian);

        return [
            'Authorization' => 'Bearer ' . $token
        ];
    }

    #[Test]
    public function librarian_can_create_a_book_copy()
    {
        $headers = $this->authenticateLibrarian();
        $book = Book::factory()->create();

        $response = $this->postJson("/api/v1/books/{$book->id}", [
            'barcode' => '123456789',
            'status' => BookCopyStatus::AVAILABLE,
        ], $headers);

        $response->assertStatus(201)
                 ->assertJson(['barcode' => '123456789']);

        $this->assertDatabaseHas('books_copies', [
            'barcode' => '123456789',
            'book_id' => $book->id,
        ]);
    }

    #[Test]
    public function librarian_can_update_a_book_copy()
    {
        $headers = $this->authenticateLibrarian();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

        $response = $this->putJson("/api/v1/books/copies/{$bookCopy->id}", [
            'status' => BookCopyStatus::RENTED,
        ], $headers);

        $response->assertStatus(200)
                 ->assertJson(['status' => BookCopyStatus::RENTED->value]);

        $this->assertDatabaseHas('books_copies', [
            'id' => $bookCopy->id,
            'status' => BookCopyStatus::RENTED->value,
        ]);
    }

    #[Test]
    public function librarian_can_delete_a_book_copy()
    {
        $headers = $this->authenticateLibrarian();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

        $response = $this->deleteJson("/api/v1/books/copies/{$bookCopy->id}", [], $headers);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Book copy deleted successfully']);

        $this->assertDatabaseMissing('books_copies', ['id' => $bookCopy->id]);
    }

    #[Test]
    public function librarian_can_view_all_books_copies()
    {
        $headers = $this->authenticateLibrarian();
        $book = Book::factory()->create();
        BookCopy::factory(3)->create(['book_id' => $book->id]);

        $response = $this->getJson("/api/v1/books/copies", $headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'barcode',
                             'status',
                             'book' => [
                                 'id',
                                 'title',
                                 'isbn',
                                 'isbn13',
                                 'published',
                                 'author',
                                 'publisher',
                                 'available_copies',
                             ],
                         ],
                     ],
                 ]);
    }

    #[Test]
    public function librarian_can_view_a_single_book_copy()
    {
        $headers = $this->authenticateLibrarian();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

        $response = $this->getJson("/api/v1/books/copies/{$bookCopy->id}", $headers);

        $response->assertStatus(200)
             ->assertJson([
                 'data' => [
                     'id' => $bookCopy->id,
                     'barcode' => $bookCopy->barcode,
                     'status' => $bookCopy->status->value,
                     'book' => [
                         'id' => $book->id,
                         'title' => $book->title,
                         'isbn' => $book->isbn,
                         'isbn13' => $book->isbn13,
                         'published' => $book->published ?? 'Unknown',
                         'author' => $book->author->name ?? 'Unknown',
                         'publisher' => $book->publisher->name ?? 'Unknown',
                         'available_copies' => $book->available_copies,
                     ],
                 ],
             ]);
    }
}
