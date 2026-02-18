<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with('creator:id,name')
            ->orderByDesc('created_at');

        if ($request->filled('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        $announcements = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $announcements->map(fn (Announcement $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'is_published' => $a->is_published,
                'published_at' => optional($a->published_at)->toDateTimeString(),
                'created_by' => $a->creator ? [
                    'id' => $a->creator->id,
                    'name' => $a->creator->name,
                ] : null,
                'created_at' => optional($a->created_at)->toDateTimeString(),
            ])->values(),
            'meta' => [
                'current_page' => $announcements->currentPage(),
                'last_page' => $announcements->lastPage(),
                'per_page' => $announcements->perPage(),
                'total' => $announcements->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'is_published' => ['boolean'],
        ]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_published' => $validated['is_published'] ?? false,
            'published_at' => ($validated['is_published'] ?? false) ? now() : null,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Duyuru olusturuldu.',
            'data' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
            ],
        ], 201);
    }

    public function show(Announcement $announcement): JsonResponse
    {
        $announcement->load('creator:id,name');

        return response()->json([
            'data' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'content' => $announcement->content,
                'is_published' => $announcement->is_published,
                'published_at' => optional($announcement->published_at)->toDateTimeString(),
                'created_by' => $announcement->creator ? [
                    'id' => $announcement->creator->id,
                    'name' => $announcement->creator->name,
                ] : null,
                'created_at' => optional($announcement->created_at)->toDateTimeString(),
            ],
        ]);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'is_published' => ['boolean'],
        ]);

        $wasPublished = $announcement->is_published;
        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_published' => $validated['is_published'] ?? $announcement->is_published,
            'published_at' => (!$wasPublished && ($validated['is_published'] ?? false)) ? now() : $announcement->published_at,
        ]);

        return response()->json([
            'message' => 'Duyuru guncellendi.',
        ]);
    }

    public function destroy(Request $request, Announcement $announcement): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $announcement->delete();

        return response()->json([
            'message' => 'Duyuru silindi.',
        ]);
    }
}
