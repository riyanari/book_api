<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserBook;
use Illuminate\Http\Request;

class UserBookController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()
            ->userBooks()
            ->with(['book.categories'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;

            $query->whereHas('book.categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'status' => ['required', 'in:owned,read,wishlist'],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'note' => ['nullable', 'string'],
        ]);

        $userBook = UserBook::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $validated['book_id'],
            ],
            [
                'status' => $validated['status'],
                'started_at' => $validated['started_at'] ?? null,
                'finished_at' => $validated['finished_at'] ?? null,
                'rating' => $validated['rating'] ?? null,
                'note' => $validated['note'] ?? null,
            ]
        );

        return response()->json($userBook->load(['book.categories']), 201);
    }

    public function show(Request $request, UserBook $userBook)
    {
        abort_if($userBook->user_id !== $request->user()->id, 403);

        return response()->json(
            $userBook->load(['book.categories', 'chapters.points'])
        );
    }

    public function update(Request $request, UserBook $userBook)
    {
        abort_if($userBook->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:owned,read,wishlist'],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'note' => ['nullable', 'string'],
        ]);

        $userBook->update($validated);

        return response()->json($userBook->load(['book.categories']));
    }

    public function destroy(Request $request, UserBook $userBook)
    {
        abort_if($userBook->user_id !== $request->user()->id, 403);

        $userBook->delete();

        return response()->json([
            'message' => 'Buku dihapus dari koleksi',
        ]);
    }
}