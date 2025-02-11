<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRental;
use App\Enums\BookCopyStatus;
use App\Enums\BookRentalStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use PHPUnit\Framework\Attributes\Test;

class BookRentalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateReader()
    {
        $reader = User::create([
            'name' => 'Test Reader',
            'email' => 'reader_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::READER,
        ]);

        $token = JWTAuth::fromUser($reader);

        return [
            'Authorization' => 'Bearer ' . $token,
            'user' => $reader
        ];
    }

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
            'Authorization' => 'Bearer ' . $token,
            'user' => $librarian
        ];
    }

    #[Test]
    public function reader_can_rent_a_single_book()
    {
        $auth = $this->authenticateReader();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => BookCopyStatus::AVAILABLE]);

        $response = $this->postJson('/api/v1/books/rent', [
            'book_ids' => [$book->id],
            'rental_days' => 30,
        ], ['Authorization' => $auth['Authorization']]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Books rented successfully']);

        $this->assertDatabaseHas('books_rentals', [
            'book_copy_id' => $bookCopy->id,
            'user_id' => $auth['user']->id,
            'status' => BookRentalStatus::ACTIVE->value,
        ]);
    }

    #[Test]
    public function reader_can_rent_multiple_books()
    {
        $auth = $this->authenticateReader();
        $books = Book::factory(2)->create();

        foreach ($books as $book) {
            BookCopy::factory()->create(['book_id' => $book->id, 'status' => BookCopyStatus::AVAILABLE]);
        }

        $response = $this->postJson('/api/v1/books/rent', [
            'book_ids' => $books->pluck('id')->toArray(),
            'rental_days' => 45,
        ], ['Authorization' => $auth['Authorization']]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Books rented successfully']);

        foreach ($books as $book) {
            $this->assertDatabaseHas('books_rentals', [
                'user_id' => $auth['user']->id,
                'status' => BookRentalStatus::ACTIVE->value,
            ]);
        }
    }

    #[Test]
    public function reader_can_return_a_single_book()
    {
        $auth = $this->authenticateReader();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => BookCopyStatus::RENTED]);

        $rental = BookRental::create([
            'user_id' => $auth['user']->id,
            'book_copy_id' => $bookCopy->id,
            'rental_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => BookRentalStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/v1/books/return', [
            'book_copy_ids' => [$bookCopy->id],
        ], ['Authorization' => $auth['Authorization']]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Books returned successfully']);

        $this->assertDatabaseHas('books_rentals', [
            'id' => $rental->id,
            'status' => BookRentalStatus::RETURNED->value,
        ]);
    }

    #[Test]
    public function reader_can_return_multiple_books()
    {
        $auth = $this->authenticateReader();
        $books = Book::factory(2)->create();
        $bookCopies = collect();

        foreach ($books as $book) {
            $bookCopies->push(BookCopy::factory()->create(['book_id' => $book->id, 'status' => BookCopyStatus::RENTED]));
        }

        foreach ($bookCopies as $copy) {
            BookRental::create([
                'user_id' => $auth['user']->id,
                'book_copy_id' => $copy->id,
                'rental_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => BookRentalStatus::ACTIVE->value,
            ]);
        }

        $response = $this->postJson('/api/v1/books/return', [
            'book_copy_ids' => $bookCopies->pluck('id')->toArray(),
        ], ['Authorization' => $auth['Authorization']]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Books returned successfully']);

        foreach ($bookCopies as $copy) {
            $this->assertDatabaseHas('books_rentals', [
                'book_copy_id' => $copy->id,
                'status' => BookRentalStatus::RETURNED->value,
            ]);
        }
    }

    #[Test]
    public function librarian_can_view_all_rentals()
    {
        $auth = $this->authenticateLibrarian();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => BookCopyStatus::RENTED]);

        BookRental::create([
            'user_id' => User::factory()->create()->id,
            'book_copy_id' => $bookCopy->id,
            'rental_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => BookRentalStatus::ACTIVE,
        ]);

        $response = $this->getJson('/api/v1/rentals', ['Authorization' => $auth['Authorization']]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'user_id', 'book_copy_id', 'rental_date', 'due_date', 'status']
                 ]);
    }

    #[Test]
    public function reader_can_view_their_rented_books()
    {
        $auth = $this->authenticateReader();
        $book = Book::factory()->create();
        $bookCopy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => BookCopyStatus::RENTED]);

        BookRental::create([
            'user_id' => $auth['user']->id,
            'book_copy_id' => $bookCopy->id,
            'rental_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => BookRentalStatus::ACTIVE,
        ]);

        $response = $this->getJson('/api/v1/books/rented', ['Authorization' => $auth['Authorization']]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'user_id',
                         'book_copy_id',
                         'rental_date',
                         'due_date',
                         'status',
                         'book_copy' => [
                             'id',
                             'barcode',
                             'status',
                             'book' => [
                                 'id',
                                 'title',
                                 'isbn',
                                 'published',
                             ],
                         ],
                     ],
                 ]);
    }
}
