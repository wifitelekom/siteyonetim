<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSiteSettingsRequest;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    private function siteData(Site $site): array
    {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'address' => $site->address,
            'city' => $site->city,
            'district' => $site->district,
            'zip_code' => $site->zip_code,
            'phone' => $site->phone,
            'tax_no' => $site->tax_no,
            'tax_office' => $site->tax_office,
            'contact_person' => $site->contact_person,
            'contact_email' => $site->contact_email,
            'contact_phone' => $site->contact_phone,
            'country' => $site->country,
            'language' => $site->language,
            'timezone' => $site->timezone,
            'currency' => $site->currency,
            'is_active' => (bool) $site->is_active,
        ];
    }

    public function show(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;

        if (!$site) {
            return response()->json([
                'data' => null,
                'message' => 'Bu hesaba bagli bir site bulunamadi. Merkezi yonetim panelinden site atayin.',
            ]);
        }

        return response()->json(['data' => $this->siteData($site)]);
    }

    public function update(UpdateSiteSettingsRequest $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404, 'Bu hesaba bagli bir site bulunamadi.');

        $site->update($request->validated());
        $site->refresh();

        return response()->json([
            'message' => 'Site ayarlari guncellendi.',
            'data' => $this->siteData($site),
        ]);
    }

    public function regional(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404);

        $validated = $request->validate([
            'country' => ['required', 'string', 'max:100'],
            'language' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
        ]);

        $site->update($validated);

        return response()->json([
            'message' => 'Bölgesel ayarlar güncellendi.',
            'data' => [
                'country' => $site->country,
                'language' => $site->language,
                'timezone' => $site->timezone,
                'currency' => $site->currency,
            ],
        ]);
    }

    public function managers(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404);

        $admins = $site->users()->role('admin')->select('id', 'name', 'email')->get();
        $auditors = $site->users()
            ->whereHas('roles', fn($q) => $q->where('name', 'auditor'))
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'data' => [
                'managers' => $admins->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
                'auditors' => $auditors->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
            ],
        ]);
    }

    public function permissions(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404);

        $permissions = array_merge(Site::defaultPermissions(), $site->permission_settings ?? []);

        return response()->json(['data' => $permissions]);
    }

    public function updatePermissions(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'super-admin']), 403);

        $site = $request->user()->site;
        abort_if(!$site, 404);

        $keys = array_keys(Site::defaultPermissions());
        $validated = $request->validate(
            collect($keys)->mapWithKeys(fn($k) => [$k => ['required', 'boolean']])->all()
        );

        $site->update(['permission_settings' => $validated]);

        return response()->json([
            'message' => 'Yetkiler güncellendi.',
            'data' => $validated,
        ]);
    }
}
