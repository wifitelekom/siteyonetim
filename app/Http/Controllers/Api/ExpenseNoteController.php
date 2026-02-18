<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseNoteResource;
use App\Models\Expense;
use App\Models\ExpenseNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseNoteController extends Controller
{
    public function index(Request $request, Expense $expense): JsonResponse
    {
        $this->authorize('viewAny', ExpenseNote::class);

        $notes = $expense->expenseNotes()
            ->with('creator:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => ExpenseNoteResource::collection($notes)->resolve(),
        ]);
    }

    public function store(Request $request, Expense $expense): JsonResponse
    {
        $this->authorize('create', ExpenseNote::class);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $note = $expense->expenseNotes()->create([
            'site_id' => $request->user()->site_id,
            'content' => $validated['content'],
            'created_by' => $request->user()->id,
        ]);

        $note->load('creator:id,name');

        return response()->json([
            'message' => 'Not eklendi.',
            'data' => new ExpenseNoteResource($note),
        ], 201);
    }

    public function destroy(Expense $expense, ExpenseNote $expenseNote): JsonResponse
    {
        $this->authorize('delete', $expenseNote);

        abort_unless($expenseNote->expense_id === $expense->id, 404);

        $expenseNote->delete();

        return response()->json([
            'message' => 'Not silindi.',
        ]);
    }
}
