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

    // Метод для создания библиотекаря и генерации токена
    protected function authenticateLibrarian()
    {
        // Создание библиотекаря (имитация консольной команды)
        $librarian = User::create([
            'name' => 'Test Librarian',
            'email' => 'librarian_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LIBRARIAN,
        ]);

        // Генерация JWT-токена для библиотекаря
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

        $response = $this->postJson("/api/v1/books/{$book->id}/copies", [
            'barcode' => '123456789',
            'status' => BookCopyStatus::AVAILABLE,
        ], $headers);

        $response->assertStatus(201)
                 ->assertJson(['barcode' => '123456789']);
    }

    #[Test]
    public function librarian_can_update_a_book_copy()
    {
        $headers = $this->authenticateLibrarian();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

        $response = $this->putJson("/api/v1/books/{$book->id}/copies/{$bookCopy->id}", [
            'status' => BookCopyStatus::RENTED,
        ], $headers);

        $response->assertStatus(200)
                 ->assertJson(['status' => BookCopyStatus::RENTED->value]);
    }

    #[Test]
    public function librarian_can_delete_a_book_copy()
    {
        $headers = $this->authenticateLibrarian();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

        $response = $this->deleteJson("/api/v1/books/{$book->id}/copies/{$bookCopy->id}", [], $headers);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Book copy deleted successfully']);

        $this->assertDatabaseMissing('books_copies', ['id' => $bookCopy->id]);
    }
}
