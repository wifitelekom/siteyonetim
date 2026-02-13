<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Site;
use App\Models\User;
use App\Services\SitePurger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SuperSiteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $query = Site::query()
            ->with(['users' => fn ($query) => $query->role('admin')->select('users.id', 'users.site_id', 'users.name', 'users.email')])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nested) use ($search) {
                $nested->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('tax_no', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sites = $query->paginate(15)->withQueryString();

        return response()->json([
            'data' => $sites->through(fn (Site $site) => $this->mapSite($site))->items(),
            'meta' => [
                'current_page' => $sites->currentPage(),
                'last_page' => $sites->lastPage(),
                'per_page' => $sites->perPage(),
                'total' => $sites->total(),
            ],
        ]);
    }

    public function meta(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        return response()->json([
            'data' => [
                'available_admins' => $this->availableAdmins(),
            ],
        ]);
    }

    public function show(Request $request, Site $site): JsonResponse
    {
        $this->authorizeManage($request);

        $site->load(['users' => fn ($query) => $query->role('admin')->select('users.id', 'users.site_id', 'users.name', 'users.email')]);
        $currentAdminId = $site->users->first()?->id;

        return response()->json([
            'data' => [
                ...$this->mapSite($site),
                'current_admin_id' => $currentAdminId,
            ],
            'meta' => [
                'available_admins' => $this->availableAdmins($site->id),
            ],
        ]);
    }

    public function store(StoreSiteRequest $request): JsonResponse
    {
        $this->authorizeManage($request);

        $data = $request->validated();

        $site = Site::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'tax_no' => $data['tax_no'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($data['admin_user_id'])) {
            $admin = User::findOrFail((int) $data['admin_user_id']);

            if ($admin->hasRole('super-admin')) {
                return response()->json([
                    'message' => 'Super admin site yoneticisi olarak atanamaz.',
                ], 422);
            }

            if ($admin->site_id && $admin->site_id !== $site->id) {
                return response()->json([
                    'message' => 'Bu kullanici baska bir siteye ait.',
                ], 422);
            }

            $admin->update(['site_id' => $site->id]);
            $admin->syncRoles(['admin']);
        } else {
            $admin = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'site_id' => $site->id,
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('admin');
        }

        $site->load(['users' => fn ($query) => $query->role('admin')->select('users.id', 'users.site_id', 'users.name', 'users.email')]);

        return response()->json([
            'message' => 'Site olusturuldu ve yonetici atandi.',
            'data' => $this->mapSite($site),
        ], 201);
    }

    public function update(UpdateSiteRequest $request, Site $site): JsonResponse
    {
        $this->authorizeManage($request);

        $data = $request->validated();

        $site->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'tax_no' => $data['tax_no'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($data['admin_user_id'])) {
            $admin = User::findOrFail((int) $data['admin_user_id']);

            if ($admin->hasRole('super-admin')) {
                return response()->json([
                    'message' => 'Super admin site yoneticisi olarak atanamaz.',
                ], 422);
            }

            if ($admin->site_id && $admin->site_id !== $site->id) {
                return response()->json([
                    'message' => 'Bu kullanici baska bir siteye ait.',
                ], 422);
            }

            $admin->update(['site_id' => $site->id]);
            $admin->syncRoles(['admin']);
        } else {
            $wantsNewAdmin = !empty($data['admin_name']) || !empty($data['admin_email']) || !empty($data['admin_password']);

            if ($wantsNewAdmin) {
                $validator = Validator::make($data, [
                    'admin_name' => ['required', 'string', 'max:255'],
                    'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
                    'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Gecersiz yonetici bilgisi.',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $admin = User::create([
                    'name' => $data['admin_name'],
                    'email' => $data['admin_email'],
                    'password' => Hash::make($data['admin_password']),
                    'site_id' => $site->id,
                    'email_verified_at' => now(),
                ]);
                $admin->assignRole('admin');
            }
        }

        $site->refresh()->load(['users' => fn ($query) => $query->role('admin')->select('users.id', 'users.site_id', 'users.name', 'users.email')]);

        return response()->json([
            'message' => 'Site guncellendi.',
            'data' => $this->mapSite($site),
        ]);
    }

    public function destroy(Request $request, Site $site, SitePurger $purger): JsonResponse
    {
        $this->authorizeManage($request);

        $purger->purge($site);

        return response()->json([
            'message' => 'Site ve bagli kayitlar silindi.',
        ]);
    }

    private function availableAdmins(?int $siteId = null): array
    {
        return User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super-admin');
            })
            ->where(function ($query) use ($siteId) {
                $query->whereNull('site_id');
                if ($siteId) {
                    $query->orWhere('site_id', $siteId);
                }
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'site_id' => $user->site_id,
            ])
            ->values()
            ->all();
    }

    private function mapSite(Site $site): array
    {
        $admin = $site->users->first();

        return [
            'id' => $site->id,
            'name' => $site->name,
            'phone' => $site->phone,
            'address' => $site->address,
            'tax_no' => $site->tax_no,
            'is_active' => (bool) $site->is_active,
            'created_at' => optional($site->created_at)->toDateString(),
            'admin' => $admin ? [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ] : null,
        ];
    }

    private function authorizeManage(Request $request): void
    {
        abort_unless($request->user()->can('sites.manage'), 403);
    }
}
