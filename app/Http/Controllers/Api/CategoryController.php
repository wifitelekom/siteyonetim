<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404);

        $categories = $site->categories()
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $grouped = $categories->groupBy('type')->map(fn($items) => $items->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'type' => $c->type,
            'color' => $c->color,
            'sort_order' => $c->sort_order,
        ])->values());

        return response()->json(['data' => $grouped]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:' . implode(',', Category::types())],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $maxOrder = $site->categories()->where('type', $validated['type'])->max('sort_order') ?? 0;

        $category = $site->categories()->create([
            ...$validated,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'message' => 'Kategori oluşturuldu.',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
                'color' => $category->color,
                'sort_order' => $category->sort_order,
            ],
        ], 201);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);
        abort_unless($category->site_id === $request->user()->site_id, 403);

        $category->delete();

        return response()->json(['message' => 'Kategori silindi.']);
    }
}
