<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;
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

        if ($user->isReader()) {
            return Book::whereHas('copies', function ($query) {
                $query->where('status', 'available');
            })->get();
        }

        return Book::all();
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * Add a new book to the library.
     *
     * @OA\Post(
     *     path="/books",
     *     tags={"book"},
     *     operationId="store",
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     * )
     */
    
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->isLibrarian()) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|regex:/^\d{9}[\dX]$/|unique:books',
            'isbn13' => 'nullable|regex:/^\d{13}$/|unique:books',
            'published' => 'nullable|date',
        ], [
            'isbn.required_without' => 'Укажите либо ISBN-10, либо ISBN-13.',
            'isbn13.required_without' => 'Укажите либо ISBN-10, либо ISBN-13.',
        ]);

        return Book::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Book::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $book = Book::findOrFail($id);
        $book->update($request->all());

        return $book;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json(['message' => 'Book deleted successfully']);
    }
}
