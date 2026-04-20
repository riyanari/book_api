<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\UserBook;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index(Request $request, UserBook $userBook)
    {
        abort_if($userBook->user_id !== $request->user()->id, 403);

        return response()->json(
            $userBook->chapters()->with('points')->orderBy('chapter_number')->get()
        );
    }

    public function store(Request $request, UserBook $userBook)
    {
        abort_if($userBook->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'chapter_number' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
        ]);

        $chapter = $userBook->chapters()->create($validated);

        return response()->json($chapter, 201);
    }

    public function show(Request $request, Chapter $chapter)
    {
        abort_if($chapter->userBook->user_id !== $request->user()->id, 403);

        return response()->json($chapter->load('points'));
    }

    public function update(Request $request, Chapter $chapter)
    {
        abort_if($chapter->userBook->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'chapter_number' => ['nullable', 'integer'],
            'title' => ['sometimes', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
        ]);

        $chapter->update($validated);

        return response()->json($chapter);
    }

    public function destroy(Request $request, Chapter $chapter)
    {
        abort_if($chapter->userBook->user_id !== $request->user()->id, 403);

        $chapter->delete();

        return response()->json([
            'message' => 'Chapter dihapus',
        ]);
    }
}