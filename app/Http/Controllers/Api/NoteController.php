<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request, User $user): JsonResponse
    {
        $this->authorize('viewAny', Note::class);

        $notes = $user->notes()
            ->with('creator:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => NoteResource::collection($notes)->resolve(),
        ]);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        $this->authorize('create', Note::class);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $note = $user->notes()->create([
            'site_id' => $request->user()->site_id,
            'content' => $validated['content'],
            'created_by' => $request->user()->id,
        ]);

        $note->load('creator:id,name');

        return response()->json([
            'message' => 'Not eklendi.',
            'data' => new NoteResource($note),
        ], 201);
    }

    public function destroy(User $user, Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        abort_unless($note->user_id === $user->id, 404);

        $note->delete();

        return response()->json([
            'message' => 'Not silindi.',
        ]);
    }
}
