<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController;

use App\Http\Controllers\JWTAuthController;
use App\Http\Middleware\JwtMiddleware;

Route::prefix('v1')->group(function () {

    Route::post('register', [JWTAuthController::class, 'register']);
    Route::post('login', [JWTAuthController::class, 'login']);

    Route::middleware([JwtMiddleware::class])->group(function () {

        Route::post('logout', [JWTAuthController::class, 'logout']);

        Route::apiResource('books', BookController::class)->only(['index', 'show']);

        Route::middleware('librarian')->group(function () {
            Route::apiResource('books', BookController::class)->except(['index', 'show']);
        });
    });
});

