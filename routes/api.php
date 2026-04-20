<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\ChapterPointController;
use App\Http\Controllers\Api\UserBookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/categories', [CategoryController::class, 'index']);

        Route::get('/books/search-local', [BookController::class, 'searchLocal']);
        Route::get('/books/search-external', [BookController::class, 'searchExternal']);
        Route::post('/books/import', [BookController::class, 'import']);
        Route::post('/books/manual', [BookController::class, 'storeManual']);
        Route::post('/books/scan-isbn', [BookController::class, 'scanIsbn']);

        Route::apiResource('user-books', UserBookController::class);

        Route::get('/user-books/{userBook}/chapters', [ChapterController::class, 'index']);
        Route::post('/user-books/{userBook}/chapters', [ChapterController::class, 'store']);
        Route::apiResource('chapters', ChapterController::class)->except(['index', 'store']);

        Route::get('/chapters/{chapter}/points', [ChapterPointController::class, 'index']);
        Route::post('/chapters/{chapter}/points', [ChapterPointController::class, 'store']);
        Route::apiResource('chapter-points', ChapterPointController::class)->except(['index', 'store']);
    });
});