<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\ChapterPointController;
use App\Http\Controllers\Api\UserBookController;
// use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Http\Request $request, $id, $hash) {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->view('email-verification.error', [
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->view('email-verification.error', [
                'message' => 'Link verifikasi tidak valid.',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return view('email-verification.success', [
                'message' => 'Email sudah diverifikasi. Silakan login ke aplikasi.',
            ]);
        }

        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));

        return view('email-verification.success', [
            'message' => 'Email berhasil diverifikasi. Silakan kembali ke aplikasi dan login.',
        ]);
    })->middleware('signed')->name('verification.verify');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/email/verification-notification', function (Request $request) {
            if ($request->user()->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email sudah diverifikasi.',
                ]);
            }

            $request->user()->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Link verifikasi berhasil dikirim ulang.',
            ]);
        })->middleware('throttle:6,1')->name('verification.send');

        Route::middleware('verified')->group(function () {
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
});