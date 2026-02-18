<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with('creator:id,name')
            ->withCount('replies')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Non-admin users only see their own tickets
        if (!$request->user()->hasAnyRole(['admin', 'super-admin'])) {
            $query->where('created_by', $request->user()->id);
        }

        $tickets = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $tickets->map(fn (SupportTicket $t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'status' => $t->status,
                'priority' => $t->priority,
                'replies_count' => (int) $t->replies_count,
                'created_by' => $t->creator ? [
                    'id' => $t->creator->id,
                    'name' => $t->creator->name,
                ] : null,
                'created_at' => optional($t->created_at)->toDateTimeString(),
                'updated_at' => optional($t->updated_at)->toDateTimeString(),
            ])->values(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'priority' => ['in:low,medium,high'],
        ]);

        $ticket = SupportTicket::create([
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'] ?? 'medium',
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Destek talebi olusturuldu.',
            'data' => ['id' => $ticket->id],
        ], 201);
    }

    public function show(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        // Non-admin can only see their own
        if (!$request->user()->hasAnyRole(['admin', 'super-admin']) && $supportTicket->created_by !== $request->user()->id) {
            abort(403);
        }

        $supportTicket->load(['creator:id,name', 'replies.user:id,name']);

        return response()->json([
            'data' => [
                'id' => $supportTicket->id,
                'subject' => $supportTicket->subject,
                'message' => $supportTicket->message,
                'status' => $supportTicket->status,
                'priority' => $supportTicket->priority,
                'created_by' => $supportTicket->creator ? [
                    'id' => $supportTicket->creator->id,
                    'name' => $supportTicket->creator->name,
                ] : null,
                'created_at' => optional($supportTicket->created_at)->toDateTimeString(),
                'replies' => $supportTicket->replies->map(fn ($r) => [
                    'id' => $r->id,
                    'message' => $r->message,
                    'user' => $r->user ? [
                        'id' => $r->user->id,
                        'name' => $r->user->name,
                    ] : null,
                    'created_at' => optional($r->created_at)->toDateTimeString(),
                ])->values(),
            ],
        ]);
    }

    public function reply(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        // Non-admin can only reply to their own
        if (!$request->user()->hasAnyRole(['admin', 'super-admin']) && $supportTicket->created_by !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $supportTicket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        // Admin reply moves to in_progress if still open
        if ($request->user()->hasAnyRole(['admin', 'super-admin']) && $supportTicket->status === 'open') {
            $supportTicket->update(['status' => 'in_progress']);
        }

        return response()->json([
            'message' => 'Cevap eklendi.',
        ]);
    }

    public function updateStatus(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $supportTicket->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Talep durumu guncellendi.',
        ]);
    }

    public function destroy(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $supportTicket->delete();

        return response()->json([
            'message' => 'Destek talebi silindi.',
        ]);
    }
}
