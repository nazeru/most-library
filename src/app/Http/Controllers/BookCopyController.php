<?php

namespace App\Http\Controllers;

use App\Enums\BookCopyStatus;
use App\Models\BookCopy;
use Illuminate\Http\Request;

class BookCopyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($bookId)
    {
        $bookCopies = BookCopy::where('book_id', $bookId)->get();
        return response()->json($bookCopies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $bookId)
    {
        $request->validate([
            'barcode' => 'required|string|unique:books_copies,barcode',
            'status' => 'required|in:' . implode(',', BookCopyStatus::values()),
        ]);

        $bookCopy = BookCopy::create([
            'book_id' => $bookId,
            'barcode' => $request->barcode,
            'status' => $request->status,
        ]);

        return response()->json($bookCopy, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($bookId, $copyId)
    {
        $bookCopy = BookCopy::where('book_id', $bookId)->findOrFail($copyId);
        return response()->json($bookCopy);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $bookId, $copyId)
    {
        $request->validate([
            'barcode' => 'sometimes|string|unique:books_copies,barcode,' . $copyId,
            'status' => 'sometimes|in:' . implode(',', BookCopyStatus::values()),
        ]);

        $bookCopy = BookCopy::where('book_id', $bookId)->findOrFail($copyId);
        $bookCopy->update($request->only(['barcode', 'status']));

        return response()->json($bookCopy);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($bookId, $copyId)
    {
        $bookCopy = BookCopy::where('book_id', $bookId)->findOrFail($copyId);
        $bookCopy->delete();
    
        return response()->json(['message' => 'Book copy deleted successfully']);
    }
}
