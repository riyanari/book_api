<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterPoint;
use Illuminate\Http\Request;

class ChapterPointController extends Controller
{
    public function index(Request $request, Chapter $chapter)
    {
        abort_if($chapter->userBook->user_id !== $request->user()->id, 403);

        return response()->json(
            $chapter->points()->orderBy('sort_order')->get()
        );
    }

    public function store(Request $request, Chapter $chapter)
    {
        abort_if($chapter->userBook->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'type' => ['required', 'in:heading,subtitle,note,quote,idea'],
            'content' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $point = $chapter->points()->create([
            'type' => $validated['type'],
            'content' => $validated['content'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json($point, 201);
    }

    public function show(Request $request, ChapterPoint $chapterPoint)
    {
        abort_if($chapterPoint->chapter->userBook->user_id !== $request->user()->id, 403);

        return response()->json($chapterPoint);
    }

    public function update(Request $request, ChapterPoint $chapterPoint)
    {
        abort_if($chapterPoint->chapter->userBook->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'type' => ['sometimes', 'in:heading,subtitle,note,quote,idea'],
            'content' => ['sometimes', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $chapterPoint->update($validated);

        return response()->json($chapterPoint);
    }

    public function destroy(Request $request, ChapterPoint $chapterPoint)
    {
        abort_if($chapterPoint->chapter->userBook->user_id !== $request->user()->id, 403);

        $chapterPoint->delete();

        return response()->json([
            'message' => 'Point dihapus',
        ]);
    }
}