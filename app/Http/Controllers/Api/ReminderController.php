<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReminderResource;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index(Request $request, User $user): JsonResponse
    {
        $this->authorize('viewAny', Reminder::class);

        $reminders = $user->reminders()
            ->with('creator:id,name')
            ->orderBy('due_date')
            ->get();

        return response()->json([
            'data' => ReminderResource::collection($reminders)->resolve(),
        ]);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        $this->authorize('create', Reminder::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['required', 'date'],
        ]);

        $reminder = $user->reminders()->create([
            'site_id' => $request->user()->site_id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'],
            'created_by' => $request->user()->id,
        ]);

        $reminder->load('creator:id,name');

        return response()->json([
            'message' => 'Hatirlatma eklendi.',
            'data' => new ReminderResource($reminder),
        ], 201);
    }

    public function update(Request $request, User $user, Reminder $reminder): JsonResponse
    {
        $this->authorize('update', $reminder);

        abort_unless($reminder->user_id === $user->id, 404);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['sometimes', 'date'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['completed'])) {
            $validated['completed_at'] = $validated['completed'] ? now() : null;
            unset($validated['completed']);
        }

        $reminder->update($validated);

        return response()->json([
            'message' => 'Hatirlatma guncellendi.',
            'data' => new ReminderResource($reminder),
        ]);
    }

    public function destroy(User $user, Reminder $reminder): JsonResponse
    {
        $this->authorize('delete', $reminder);

        abort_unless($reminder->user_id === $user->id, 404);

        $reminder->delete();

        return response()->json([
            'message' => 'Hatirlatma silindi.',
        ]);
    }
}
