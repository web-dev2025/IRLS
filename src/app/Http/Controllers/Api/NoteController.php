<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page_id'     => 'required|exists:pages,id',
            'phrase'      => 'required|string|max:500',
            'translation' => 'nullable|string|max:1000',
            'comment'     => 'nullable|string|max:2000',
            'x'           => 'required|numeric|min:0|max:100',
            'y'           => 'required|numeric|min:0|max:100',
            'width'       => 'required|numeric|min:0|max:100',
            'height'      => 'required|numeric|min:0|max:100',
        ]);

        $note = Note::create($data);

        return response()->json($note, 201);
    }

    public function toggleLearned(Note $note): JsonResponse
    {
        $note->update(['is_learned' => !$note->is_learned]);

        return response()->json(['is_learned' => $note->is_learned]);
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return response()->json(['ok' => true]);
    }
}
