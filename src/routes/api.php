<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController;

use App\Http\Controllers\JWTAuthController;
use App\Http\Middleware\JwtMiddleware;

Route::prefix('v1')->group(function () {

    Route::post('register', [JWTAuthController::class, 'register']);
    Route::post('login', [JWTAuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [JWTAuthController::class, 'logout']);
        Route::post('refresh', [JWTAuthController::class, 'refresh']);

        Route::get('books', [BookController::class, 'index']);
        
        Route::middleware('role:reader')->group(function () {
            Route::post('books', [BookController::class, 'get']);
            Route::post('books', [BookController::class, 'return']);
        });

        Route::middleware('role:librarian')->group(function () {
            Route::post('books', [BookController::class, 'store']);
            Route::put('books', [BookController::class, 'update']);
            Route::delete('books', [BookController::class, 'destroy']);
        });

    });
});

