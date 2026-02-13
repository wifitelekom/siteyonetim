<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Apartment::class);

        $query = Apartment::query()
            ->with([
                'owners:id,name',
                'tenants:id,name',
            ])
            ->withCount('users');

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
            ->orderBy('block')
            ->orderBy('floor')
            ->orderBy('number')
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'data' => $apartments->through(fn (Apartment $apartment) => $this->mapApartment($apartment))->items(),
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
            'data' => $this->mapApartment($apartment),
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
                ...$this->mapApartment($apartment),
                'users' => $apartment->users->map(function (User $user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'relation_type' => $user->pivot?->relation_type,
                        'relation_label' => $user->pivot?->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiraci',
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
            'data' => $this->mapApartment($apartment),
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

    public function addResident(Request $request, Apartment $apartment): JsonResponse
    {
        $this->authorize('update', $apartment);

        $siteId = $request->user()->site_id;

        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'relation_type' => ['required', 'in:owner,tenant'],
            'start_date' => ['nullable', 'date'],
        ]);

        $apartment->users()->syncWithoutDetaching([
            $validated['user_id'] => [
                'relation_type' => $validated['relation_type'],
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

    private function mapApartment(Apartment $apartment): array
    {
        $owner = $apartment->owners->first();
        $tenant = $apartment->tenants->first();

        return [
            'id' => $apartment->id,
            'block' => $apartment->block,
            'floor' => $apartment->floor,
            'number' => $apartment->number,
            'm2' => $apartment->m2 !== null ? (float) $apartment->m2 : null,
            'arsa_payi' => $apartment->arsa_payi !== null ? (float) $apartment->arsa_payi : null,
            'is_active' => (bool) $apartment->is_active,
            'full_label' => $apartment->full_label,
            'resident_count' => (int) ($apartment->users_count ?? 0),
            'current_owner' => $owner ? [
                'id' => $owner->id,
                'name' => $owner->name,
            ] : null,
            'current_tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ] : null,
        ];
    }
}
