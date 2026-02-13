<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()
            ->with(['roles:id,name'])
            ->withCount('apartments');

        if ($request->filled('role')) {
            $role = (string) $request->string('role');
            $query->whereHas('roles', fn ($nested) => $nested->where('name', $role));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nested) use ($search) {
                $nested->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('tc_kimlik', 'like', '%' . $search . '%');
            });
        }

        $users = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'data' => $users->through(fn (User $user) => $this->mapUser($user))->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function meta(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $apartments = Apartment::query()
            ->where('is_active', true)
            ->orderBy('block')
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->map(fn (Apartment $apartment) => [
                'id' => $apartment->id,
                'label' => $apartment->full_label,
            ])->values();

        return response()->json([
            'data' => [
                'roles' => [
                    ['value' => 'admin', 'label' => 'Yonetici'],
                    ['value' => 'owner', 'label' => 'Ev Sahibi'],
                    ['value' => 'tenant', 'label' => 'Kiraci'],
                    ['value' => 'vendor', 'label' => 'Tedarikci'],
                ],
                'apartments' => $apartments,
                'can_manage_roles' => $request->user()->can('users.manage'),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        if (!$request->user()->site_id) {
            return response()->json([
                'message' => 'Aktif bir site baglami bulunamadi.',
            ], 422);
        }

        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'tc_kimlik' => $validated['tc_kimlik'] ?? null,
            'password' => Hash::make($validated['password']),
            'site_id' => $request->user()->site_id,
        ]);

        $user->assignRole($validated['role']);
        $user->loadMissing(['roles:id,name']);
        $user->loadCount('apartments');

        return response()->json([
            'message' => 'Kullanici olusturuldu.',
            'data' => $this->mapUser($user),
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user->load([
            'roles:id,name',
            'apartments' => fn ($query) => $query
                ->orderBy('block')
                ->orderBy('floor')
                ->orderBy('number'),
        ]);

        $availableApartments = Apartment::query()
            ->where('site_id', $request->user()->site_id)
            ->where('is_active', true)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->orderBy('block')
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->map(fn (Apartment $apartment) => [
                'id' => $apartment->id,
                'label' => $apartment->full_label,
            ])->values();

        return response()->json([
            'data' => $this->mapUser($user, true),
            'meta' => [
                'available_apartments' => $availableApartments,
                'roles' => [
                    ['value' => 'admin', 'label' => 'Yonetici'],
                    ['value' => 'owner', 'label' => 'Ev Sahibi'],
                    ['value' => 'tenant', 'label' => 'Kiraci'],
                    ['value' => 'vendor', 'label' => 'Tedarikci'],
                ],
                'relation_types' => [
                    ['value' => 'owner', 'label' => 'Ev Sahibi'],
                    ['value' => 'tenant', 'label' => 'Kiraci'],
                ],
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validated();

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'tc_kimlik' => $validated['tc_kimlik'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);
        $user->syncRoles([$validated['role']]);
        $user->refresh()->loadMissing(['roles:id,name']);
        $user->loadCount('apartments');

        return response()->json([
            'message' => 'Kullanici guncellendi.',
            'data' => $this->mapUser($user),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Kendinizi silemezsiniz.',
            ], 422);
        }

        $this->authorize('delete', $user);

        $user->delete();

        return response()->json([
            'message' => 'Kullanici silindi.',
        ]);
    }

    public function addApartment(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $siteId = $request->user()->site_id;

        $validated = $request->validate([
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'relation_type' => ['required', 'in:owner,tenant'],
            'start_date' => ['nullable', 'date'],
        ]);

        $user->apartments()->syncWithoutDetaching([
            $validated['apartment_id'] => [
                'relation_type' => $validated['relation_type'],
                'start_date' => $validated['start_date'] ?? now()->toDateString(),
            ],
        ]);

        return response()->json([
            'message' => 'Daire iliskisi eklendi.',
        ]);
    }

    public function removeApartment(Request $request, User $user, Apartment $apartment): JsonResponse
    {
        $this->authorize('update', $user);

        if ($apartment->site_id !== $request->user()->site_id) {
            abort(404);
        }

        $user->apartments()->detach($apartment->id);

        return response()->json([
            'message' => 'Daire iliskisi kaldirildi.',
        ]);
    }

    private function mapUser(User $user, bool $includeApartments = false): array
    {
        $roles = $user->roles->pluck('name')->values();
        $primaryRole = $roles->first();

        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'tc_kimlik' => $user->tc_kimlik,
            'role' => $primaryRole,
            'role_label' => $this->roleLabel($primaryRole),
            'roles' => $roles,
            'created_at' => optional($user->created_at)->toDateString(),
            'apartment_count' => (int) ($user->apartments_count ?? 0),
        ];

        if ($includeApartments) {
            $payload['apartments'] = $user->apartments->map(function (Apartment $apartment) {
                return [
                    'id' => $apartment->id,
                    'label' => $apartment->full_label,
                    'relation_type' => $apartment->pivot?->relation_type,
                    'relation_label' => $apartment->pivot?->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiraci',
                    'start_date' => $apartment->pivot?->start_date,
                    'end_date' => $apartment->pivot?->end_date,
                ];
            })->values();
            $payload['apartment_count'] = $payload['apartments']->count();
        }

        return $payload;
    }

    private function roleLabel(?string $role): string
    {
        return match ($role) {
            'admin' => 'Yonetici',
            'owner' => 'Ev Sahibi',
            'tenant' => 'Kiraci',
            'vendor' => 'Tedarikci',
            default => 'Rol Yok',
        };
    }
}
