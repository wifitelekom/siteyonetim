<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApartmentGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApartmentGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404);

        $groups = $site->apartmentGroups()
            ->withCount('apartments')
            ->orderBy('name')
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'description' => $g->description,
                'multiplier' => $g->multiplier,
                'apartments_count' => $g->apartments_count,
            ]);

        return response()->json(['data' => $groups]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'multiplier' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]);

        $group = $site->apartmentGroups()->create($validated);

        return response()->json([
            'message' => 'Daire grubu oluşturuldu.',
            'data' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'multiplier' => $group->multiplier,
                'apartments_count' => 0,
            ],
        ], 201);
    }

    public function update(Request $request, ApartmentGroup $apartmentGroup): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);
        abort_unless($apartmentGroup->site_id === $request->user()->site_id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'multiplier' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]);

        $apartmentGroup->update($validated);

        return response()->json([
            'message' => 'Daire grubu güncellendi.',
            'data' => [
                'id' => $apartmentGroup->id,
                'name' => $apartmentGroup->name,
                'description' => $apartmentGroup->description,
                'multiplier' => $apartmentGroup->multiplier,
            ],
        ]);
    }

    public function destroy(Request $request, ApartmentGroup $apartmentGroup): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);
        abort_unless($apartmentGroup->site_id === $request->user()->site_id, 403);

        $apartmentGroup->apartments()->update(['apartment_group_id' => null]);
        $apartmentGroup->delete();

        return response()->json(['message' => 'Daire grubu silindi.']);
    }
}
