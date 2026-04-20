<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\BookUpsertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    public function searchLocal(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:1'],
        ]);

        $q = $request->q;

        $books = Book::with('categories')
            ->where('title', 'like', "%{$q}%")
            ->orWhere('author', 'like', "%{$q}%")
            ->orWhere('isbn', 'like', "%{$q}%")
            ->limit(20)
            ->get();

        return response()->json($books);
    }

    public function searchExternal(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        try {
            $response = Http::timeout(20)->get('https://openlibrary.org/search.json', [
                'q' => $request->q,
                'limit' => 20,
            ]);

            if (! $response->successful()) {
                return response()->json([
                    'message' => 'Gagal mengambil data buku eksternal',
                    'status' => $response->status(),
                    'error' => $response->body(),
                ], 500);
            }

            $items = collect($response->json('docs', []))->map(function ($item) {
                $coverId = $item['cover_i'] ?? null;

                return [
                    'source' => 'open_library',
                    'source_id' => $item['key'] ?? null,
                    'isbn' => isset($item['isbn']) && is_array($item['isbn']) ? ($item['isbn'][0] ?? null) : null,
                    'title' => $item['title'] ?? null,
                    'author' => isset($item['author_name']) ? implode(', ', $item['author_name']) : null,
                    'publisher' => isset($item['publisher']) ? ($item['publisher'][0] ?? null) : null,
                    'published_year' => isset($item['first_publish_year']) ? (string) $item['first_publish_year'] : null,
                    'description' => null,
                    'cover_url' => $coverId
                        ? "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg"
                        : null,
                ];
            })->values();

            return response()->json($items);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil data buku eksternal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request, BookUpsertService $service)
    {
        $validated = $request->validate([
            'source' => ['nullable', 'string'],
            'source_id' => ['nullable', 'string'],
            'isbn' => ['nullable', 'string'],
            'title' => ['required', 'string'],
            'author' => ['nullable', 'string'],
            'publisher' => ['nullable', 'string'],
            'published_year' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $book = $service->findOrCreate($validated);

        if (! empty($validated['category_ids'])) {
            $book->categories()->syncWithoutDetaching($validated['category_ids']);
        }

        return response()->json($book->load('categories'), 201);
    }

    public function storeManual(Request $request, BookUpsertService $service)
    {
        $validated = $request->validate([
            'isbn' => ['nullable', 'string'],
            'title' => ['required', 'string'],
            'author' => ['nullable', 'string'],
            'publisher' => ['nullable', 'string'],
            'published_year' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $defaultCoverUrl = asset('storage/defaults/book-cover.svg');

        $validated['cover_url'] = filled($validated['cover_url'] ?? null)
            ? $validated['cover_url']
            : $defaultCoverUrl;

        $book = $service->findOrCreate([
            ...$validated,
            'source' => 'manual',
            'source_id' => null,
        ]);

        if (! empty($validated['category_ids'])) {
            $book->categories()->syncWithoutDetaching($validated['category_ids']);
        }

        return response()->json($book->load('categories'), 201);
    }

    public function scanIsbn(Request $request, BookUpsertService $service)
    {
        $validated = $request->validate([
            'isbn' => ['required', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $local = Book::with('categories')->where('isbn', $validated['isbn'])->first();
        if ($local) {
            if (! empty($validated['category_ids'])) {
                $local->categories()->syncWithoutDetaching($validated['category_ids']);
            }

            return response()->json($local->load('categories'));
        }

        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:' . $validated['isbn'],
            'maxResults' => 1,
        ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Gagal mencari ISBN',
            ], 500);
        }

        $item = collect($response->json('items', []))->first();

        if (! $item) {
            $book = $service->findOrCreate([
                'source' => 'barcode_manual',
                'source_id' => null,
                'isbn' => $validated['isbn'],
                'title' => 'Unknown Book',
                'author' => null,
            ]);

            if (! empty($validated['category_ids'])) {
                $book->categories()->syncWithoutDetaching($validated['category_ids']);
            }

            return response()->json($book->load('categories'));
        }

        $info = $item['volumeInfo'] ?? [];
        $identifiers = collect($info['industryIdentifiers'] ?? []);
        $isbn13 = $identifiers->firstWhere('type', 'ISBN_13')['identifier'] ?? null;
        $isbn10 = $identifiers->firstWhere('type', 'ISBN_10')['identifier'] ?? null;

        $book = $service->findOrCreate([
            'source' => 'google_books',
            'source_id' => $item['id'] ?? null,
            'isbn' => $isbn13 ?: $isbn10 ?: $validated['isbn'],
            'title' => $info['title'] ?? 'Unknown Book',
            'author' => isset($info['authors']) ? implode(', ', $info['authors']) : null,
            'publisher' => $info['publisher'] ?? null,
            'published_year' => $info['publishedDate'] ?? null,
            'description' => $info['description'] ?? null,
            'cover_url' => $info['imageLinks']['thumbnail'] ?? null,
        ]);

        if (! empty($validated['category_ids'])) {
            $book->categories()->syncWithoutDetaching($validated['category_ids']);
        }

        return response()->json($book->load('categories'));
    }
}