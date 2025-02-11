<?php

namespace App\Http\Controllers;

use App\Enums\BookCopyStatus;
use App\Http\Resources\BookCopyResource;
use App\Models\BookCopy;
use Illuminate\Http\Request;

class BookCopyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookCopies = BookCopy::with('book')->get();
        return BookCopyResource::collection($bookCopies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $bookId)
    {
        $request->validate([
            'barcode' => 'required|string|unique:books_copies,barcode',
            'status' => 'nullable|in:' . implode(',', BookCopyStatus::values()),
        ]);

        $bookCopy = BookCopy::create([
            'book_id' => $bookId,
            'barcode' => $request->barcode,
            'status' => $request->status ?? BookCopyStatus::AVAILABLE,
        ]);

        return response()->json($bookCopy, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bookCopy = BookCopy::with('book')->find($id);

        if (!$bookCopy) {
            return response()->json(['error' => 'Book copy not found'], 404);
        }

        return new BookCopyResource($bookCopy);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $copyId)
    {
        $request->validate([
            'barcode' => 'sometimes|string|unique:books_copies,barcode,' . $copyId,
            'status' => 'sometimes|in:' . implode(',', BookCopyStatus::values()),
        ]);

        $bookCopy = BookCopy::find($copyId);

        if (!$bookCopy) {
            return response()->json(['error' => 'Book copy not found'], 403);
        }

        $bookCopy->update($request->only(['barcode', 'status']));

        return response()->json($bookCopy);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($copyId)
    {
        $bookCopy = BookCopy::find($copyId);

        if (!$bookCopy) {
            return response()->json(['error' => 'Book copy not found'], 403);
        }
        
        $bookCopy->delete();
    
        return response()->json(['message' => 'Book copy deleted successfully']);
    }
}
