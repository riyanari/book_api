<?php

namespace App\Services;

use App\Models\Book;
use App\Support\BookNormalizer;

class BookUpsertService
{
    public function findOrCreate(array $data): Book
    {
        $isbn = $data['isbn'] ?? null;
        $source = $data['source'] ?? null;
        $sourceId = $data['source_id'] ?? null;
        $title = $data['title'] ?? null;
        $author = $data['author'] ?? null;

        $normalizedTitle = BookNormalizer::text($title);
        $normalizedAuthor = BookNormalizer::text($author);

        $book = null;

        if ($isbn) {
            $book = Book::where('isbn', $isbn)->first();
        }

        if (! $book && $source && $sourceId) {
            $book = Book::where('source', $source)
                ->where('source_id', $sourceId)
                ->first();
        }

        if (! $book && $normalizedTitle && $normalizedAuthor) {
            $book = Book::where('normalized_title', $normalizedTitle)
                ->where('normalized_author', $normalizedAuthor)
                ->first();
        }

        if ($book) {
            $book->update($this->mergeBookData($book, $data, $normalizedTitle, $normalizedAuthor));
            return $book->fresh();
        }

        return Book::create([
            'source' => $source,
            'source_id' => $sourceId,
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'publisher' => $data['publisher'] ?? null,
            'published_year' => $data['published_year'] ?? null,
            'description' => $data['description'] ?? null,
            'cover_url' => $data['cover_url'] ?? null,
            'normalized_title' => $normalizedTitle,
            'normalized_author' => $normalizedAuthor,
        ]);
    }

    protected function mergeBookData(Book $book, array $data, ?string $normalizedTitle, ?string $normalizedAuthor): array
    {
        return [
            'source' => $book->source ?: ($data['source'] ?? null),
            'source_id' => $book->source_id ?: ($data['source_id'] ?? null),
            'isbn' => $book->isbn ?: ($data['isbn'] ?? null),
            'title' => $book->title ?: ($data['title'] ?? null),
            'author' => $book->author ?: ($data['author'] ?? null),
            'publisher' => $book->publisher ?: ($data['publisher'] ?? null),
            'published_year' => $book->published_year ?: ($data['published_year'] ?? null),
            'description' => $book->description ?: ($data['description'] ?? null),
            'cover_url' => $book->cover_url ?: ($data['cover_url'] ?? null),
            'normalized_title' => $book->normalized_title ?: $normalizedTitle,
            'normalized_author' => $book->normalized_author ?: $normalizedAuthor,
        ];
    }
}