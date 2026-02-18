<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddResidentRequest;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Apartment::class);

        $query = Apartment::query()
            ->with([
                'owners:id,name',
                'tenants:id,name',
                'group:id,name',
            ])
            ->withCount('users')
            ->withSum('charges as total_charged', 'amount')
            ->withSum('charges as total_paid', 'paid_amount');

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nested) use ($search) {
                $nested->where('block', 'like', '%' . $search . '%')
                    ->orWhere('number', 'like', '%' . $search . '%')
                    ->orWhereRaw("CONCAT(COALESCE(block, ''), ' ', floor, ' ', number) like ?", ['%' . $search . '%']);

                if (is_numeric($search)) {
                    $nested->orWhere('floor', (int) $search);
                }
            });
        }

        $apartments = $query
            ->orderedForDisplay()
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'data' => ApartmentResource::collection($apartments)->resolve(),
            'meta' => [
                'current_page' => $apartments->currentPage(),
                'last_page' => $apartments->lastPage(),
                'per_page' => $apartments->perPage(),
                'total' => $apartments->total(),
            ],
        ]);
    }

    public function meta(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Apartment::class);

        $siteId = $request->user()?->site_id;
        $users = User::query()
            ->when($siteId, fn ($query) => $query->where('site_id', $siteId))
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values();

        return response()->json([
            'data' => [
                'relation_types' => [
                    ['value' => 'owner', 'label' => 'Ev Sahibi'],
                    ['value' => 'tenant', 'label' => 'Kiraci'],
                ],
                'users' => $users,
            ],
        ]);
    }

    public function store(StoreApartmentRequest $request): JsonResponse
    {
        $this->authorize('create', Apartment::class);

        $payload = $request->validated();
        $payload['is_active'] = (bool) ($payload['is_active'] ?? true);

        $apartment = Apartment::create($payload);

        return response()->json([
            'message' => 'Daire olusturuldu.',
            'data' => new ApartmentResource($apartment),
        ], 201);
    }

    public function show(Request $request, Apartment $apartment): JsonResponse
    {
        $this->authorize('view', $apartment);

        $apartment->load([
            'users' => fn ($query) => $query->orderBy('name'),
            'charges' => fn ($query) => $query
                ->with(['account', 'creator'])
                ->orderByDesc('due_date')
                ->limit(10),
        ]);

        $availableUsers = User::query()
            ->where('site_id', $request->user()->site_id)
            ->whereDoesntHave('apartments', function ($query) use ($apartment) {
                $query->where('apartments.id', $apartment->id);
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values();

        return response()->json([
            'data' => [
                ...((new ApartmentResource($apartment))->resolve()),
                'users' => $apartment->users->map(function (User $user) {
                    $familyRoleLabels = [
                        'spouse' => 'Es',
                        'child' => 'Cocuk',
                        'parent' => 'Anne/Baba',
                        'sibling' => 'Kardes',
                        'other' => 'Diger',
                    ];

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'relation_type' => $user->pivot?->relation_type,
                        'relation_label' => $user->pivot?->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiraci',
                        'family_role' => $user->pivot?->family_role,
                        'family_role_label' => $familyRoleLabels[$user->pivot?->family_role] ?? null,
                        'start_date' => $user->pivot?->start_date,
                        'end_date' => $user->pivot?->end_date,
                    ];
                })->values(),
                'charges' => $apartment->charges->map(function ($charge) {
                    return [
                        'id' => $charge->id,
                        'period' => $charge->period,
                        'due_date' => optional($charge->due_date)->toDateString(),
                        'amount' => (float) $charge->amount,
                        'paid_amount' => (float) $charge->paid_amount,
                        'remaining' => (float) $charge->remaining,
                        'status' => $charge->status?->value ?? (string) $charge->status,
                        'description' => $charge->description,
                        'account' => $charge->account ? [
                            'id' => $charge->account->id,
                            'name' => $charge->account->full_name,
                        ] : null,
                    ];
                })->values(),
            ],
            'meta' => [
                'available_users' => $availableUsers,
                'relation_types' => [
                    ['value' => 'owner', 'label' => 'Ev Sahibi'],
                    ['value' => 'tenant', 'label' => 'Kiraci'],
                ],
                'family_roles' => [
                    ['value' => 'spouse', 'label' => 'Es'],
                    ['value' => 'child', 'label' => 'Cocuk'],
                    ['value' => 'parent', 'label' => 'Anne/Baba'],
                    ['value' => 'sibling', 'label' => 'Kardes'],
                    ['value' => 'other', 'label' => 'Diger'],
                ],
            ],
        ]);
    }

    public function update(UpdateApartmentRequest $request, Apartment $apartment): JsonResponse
    {
        $this->authorize('update', $apartment);

        $payload = $request->validated();
        if (array_key_exists('is_active', $payload)) {
            $payload['is_active'] = (bool) $payload['is_active'];
        }

        $apartment->update($payload);
        $apartment->refresh();

        return response()->json([
            'message' => 'Daire guncellendi.',
            'data' => new ApartmentResource($apartment),
        ]);
    }

    public function destroy(Apartment $apartment): JsonResponse
    {
        $this->authorize('delete', $apartment);

        if ($apartment->charges()->exists()) {
            return response()->json([
                'message' => 'Bu daireye bagli tahakkuklar var, silinemez.',
            ], 422);
        }

        $apartment->delete();

        return response()->json([
            'message' => 'Daire silindi.',
        ]);
    }

    public function addResident(AddResidentRequest $request, Apartment $apartment): JsonResponse
    {
        $this->authorize('update', $apartment);

        $validated = $request->validated();

        $apartment->users()->syncWithoutDetaching([
            $validated['user_id'] => [
                'relation_type' => $validated['relation_type'],
                'family_role' => $validated['family_role'] ?? null,
                'start_date' => $validated['start_date'] ?? now()->toDateString(),
            ],
        ]);

        return response()->json([
            'message' => 'Sakin eklendi.',
        ]);
    }

    public function removeResident(Apartment $apartment, User $user): JsonResponse
    {
        $this->authorize('update', $apartment);

        $apartment->users()->detach($user->id);

        return response()->json([
            'message' => 'Sakin kaldirildi.',
        ]);
    }
}
