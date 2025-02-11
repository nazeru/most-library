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
        $user = $request->user();
        if (!$user->isLibrarian()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|regex:/^\d{9}[\dX]$/|unique:books',
            'isbn13' => 'nullable|regex:/^\d{13}$/|unique:books',
            'published' => 'nullable|date',
        ]);

        if (empty($request->isbn) && empty($request->isbn13)) {
            return response()->json(['error' => 'Either ISBN-10 or ISBN-13 is required.'], 422);
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
        $user = $request->user();
        if (!$user->isLibrarian()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

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
        $user = $request->user();
        if (!$user->isLibrarian()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $book = Book::find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
}
