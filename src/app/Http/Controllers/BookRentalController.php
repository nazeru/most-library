<?php

namespace App\Http\Controllers;

use App\Enums\BookCopyStatus;
use App\Enums\BookRentalStatus;
use App\Models\BookCopy;
use App\Models\BookRental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookRentalController extends Controller
{

    public function rentBooks(Request $request)
    {
        $request->validate([
            'book_ids' => 'required|min:1',
            'book_ids.*' => 'exists:books,id',
            'rental_days' => 'nullable|integer|min:30|max:60',
        ]);

        $user = $request->user();
        $rentalDays = $request->rental_days ?? 30;

        $bookIds = is_array($request->book_ids) ? $request->book_ids : [$request->book_ids];

        foreach ($bookIds as $bookId) {
            // Найдем первую доступную копию книги
            $bookCopy = BookCopy::where('book_id', $bookId)
                ->where('status', BookCopyStatus::AVAILABLE)
                ->first();

            if (!$bookCopy) {
                return response()->json(['error' => 'No available copies for Book'], 404);
            }

            DB::transaction(function () use ($user, $rentalDays, $bookCopy) {
                // Создание записи об аренде
                BookRental::create([
                    'user_id' => $user->id,
                    'book_copy_id' => $bookCopy->id,
                    'rental_date' => now(),
                    'due_date' => now()->addDays($rentalDays),
                    'status' => BookRentalStatus::ACTIVE,
                ]);

                // Обновление статуса копии книги
                $bookCopy->update(['status' => BookCopyStatus::RENTED]);
            });
        }

        return response()->json(['message' => 'Books rented successfully']);
    }

    public function returnBooks(Request $request)
    {
        $request->validate([
            'book_copy_ids' => 'required|min:1',
            'book_copy_ids.*' => 'exists:books_copies,id',
        ]);
    
        $user = $request->user();
        $bookCopyIds = is_array($request->book_copy_ids)
            ? $request->book_copy_ids
            : [$request->book_copy_ids];
    
        foreach ($bookCopyIds as $bookCopyId) {
            $rental = BookRental::where('book_copy_id', $bookCopyId)
                ->where(function ($query) {
                    $query->where('status', BookRentalStatus::ACTIVE)
                        ->orWhere('status', BookRentalStatus::OVERDUE);
                })
                ->first();
    
            if (!$rental) {
                return response()->json(['error' => 'No available rented book'], 404);
            }
    
            DB::transaction(function () use ($rental, $bookCopyId) {
                $returnStatus = now()->gt($rental->due_date) 
                    ? BookRentalStatus::RETURNED_WITH_OVERDUE 
                    : BookRentalStatus::RETURNED;
    
                $rental->update([
                    'return_date' => now(),
                    'status' => $returnStatus,
                ]);
    
                $bookCopy = BookCopy::findOrFail($bookCopyId);
                $bookCopy->update(['status' => BookCopyStatus::AVAILABLE]);
            });
        }
    
        return response()->json(['message' => 'Books returned successfully']);
    }
    

    public function getAllRentals()
    {
        $rentals = BookRental::with(['bookCopy', 'user'])->get();
        return response()->json($rentals);
    }

    public function getRented(Request $request)
    {
        $user = $request->user();

        $rentedBooks = BookRental::with('bookCopy.book')
            ->where('user_id', $user->id)
            ->where('status', BookRentalStatus::ACTIVE)
            ->orWhere('status', BookRentalStatus::OVERDUE)
            ->get();

        return response()->json($rentedBooks);
    }
}
