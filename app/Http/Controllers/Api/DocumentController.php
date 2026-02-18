<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService
    ) {}

    public function index(Request $request, User $user): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        $documents = $user->documents()
            ->with('uploader:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => DocumentResource::collection($documents)->resolve(),
        ]);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        $this->authorize('create', Document::class);

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $document = $this->documentService->upload($user, $request->file('file'));
        $document->load('uploader:id,name');

        return response()->json([
            'message' => 'Evrak yuklendi.',
            'data' => new DocumentResource($document),
        ], 201);
    }

    public function download(User $user, Document $document)
    {
        $this->authorize('download', $document);

        abort_unless($document->user_id === $user->id, 404);

        return $this->documentService->download($document);
    }

    public function destroy(User $user, Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        abort_unless($document->user_id === $user->id, 404);

        $this->documentService->delete($document);

        return response()->json([
            'message' => 'Evrak silindi.',
        ]);
    }
}
