<?php

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookCopyController;
use App\Http\Controllers\BookRentalController;
use App\Http\Controllers\JWTAuthController;
use App\Http\Middleware\UserRoleMiddleware;

Route::prefix('v1')->group(function () {

    Route::post('register', [JWTAuthController::class, 'register']);
    Route::post('login', [JWTAuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [JWTAuthController::class, 'logout']);
        Route::post('refresh', [JWTAuthController::class, 'refresh']);

        Route::get('books', [BookController::class, 'index']);
        
        Route::middleware(UserRoleMiddleware::class.':reader')->group(function () {
            Route::post('books/rent', [BookRentalController::class, 'rentBooks']);  
            Route::post('books/return', [BookRentalController::class, 'returnBooks']);
            Route::get('books/rented', [BookRentalController::class, 'getRented']);
        });

        Route::middleware(UserRoleMiddleware::class.':librarian')->group(function () {
            Route::post('books', [BookController::class, 'store']);
            Route::put('books/{id}', [BookController::class, 'update']);
            Route::delete('books/{id}', [BookController::class, 'destroy']);

            Route::get('books/copies', [BookCopyController::class, 'index']);
            Route::get('books/copies/{}', [BookCopyController::class, 'show']);

            Route::get('books/{book}/copies', [BookCopyController::class, 'index']);
            Route::get('books/{book}/copies/{copy}', [BookCopyController::class, 'show']);

            Route::post('books/{book}/copies', [BookCopyController::class, 'store']);
            Route::put('books/{book}/copies/{copy}', [BookCopyController::class, 'update']);
            Route::delete('books/{book}/copies/{copy}', [BookCopyController::class, 'destroy']);

            Route::get('rentals', [BookRentalController::class, 'getAllRentals']);
        });

        Route::get('books/{id}', [BookController::class, 'show']);
    });
});

