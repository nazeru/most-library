<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Library API Documentation",
 *     version="1.0.0",
 * )
 */
class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isLibrarian()) {
            return response()->json(Book::all(), 200);
        }

        $books = Book::whereHas('copies', function ($query) {
            $query->where('status', 'available');
        })->get();

        return response()->json($books, 200);
    }

    /**
     * Add a new book to the library.
     *
     * @OA\Post(
     *     path="/books",
     *     tags={"book"},
     *     operationId="store",
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     * )
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|regex:/^\d{9}[\dX]$/|unique:books',
            'isbn13' => 'nullable|regex:/^\d{13}$/|unique:books',
            'published' => 'nullable|date',
        ]);

        if (empty($request->isbn) && empty($request->isbn13)) {
            return response()->json(['error' => 'Either ISBN-10 or ISBN-13 is required.'], 422);
        }

        if ($request->filled('isbn') && empty($request->isbn13)) {
            $request->merge(['isbn13' => $this->convertIsbn10ToIsbn13($request->isbn)]);
        } elseif ($request->filled('isbn13') && empty($request->isbn)) {
            $request->merge(['isbn' => $this->convertIsbn13ToIsbn10($request->isbn13)]);
        }

        $book = Book::create($request->all());

        return response()->json($book, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        if (!$user->isLibrarian()) {
            $availableCopies = $book->copies()->where('status', 'available')->exists();

            if (!$availableCopies) {
                return response()->json(['error' => 'This book is not available'], 403);
            }
        }

        return response()->json($book, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $book = Book::find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        $book->update($request->all());

        return response()->json($book, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

        $book = Book::find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], 200);
    }

    private function convertIsbn10ToIsbn13($isbn10)
    {
        $isbn = '978' . substr($isbn10, 0, 9);
        $checkDigit = $this->calculateIsbn13CheckDigit($isbn);
        return $isbn . $checkDigit;
    }

    private function convertIsbn13ToIsbn10($isbn13)
    {
        if (substr($isbn13, 0, 3) !== '978') {
            return null;
        }

        $isbn = substr($isbn13, 3, 9);
        $checkDigit = $this->calculateIsbn10CheckDigit($isbn);
        return $isbn . $checkDigit;
    }

    private function calculateIsbn13CheckDigit($isbn)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += ($i % 2 === 0) ? (int) $isbn[$i] : (int) $isbn[$i] * 3;
        }
        $remainder = $sum % 10;
        return ($remainder === 0) ? 0 : 10 - $remainder;
    }

    private function calculateIsbn10CheckDigit($isbn)
    {
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $isbn[$i]) * (10 - $i);
        }
        $remainder = 11 - ($sum % 11);
        if ($remainder === 10) {
            return 'X';
        } elseif ($remainder === 11) {
            return '0';
        }
        return (string) $remainder;
    }
}
