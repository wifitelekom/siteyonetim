<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSiteSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
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

        return response()->json([
            'data' => [
                'id' => $site->id,
                'name' => $site->name,
                'address' => $site->address,
                'phone' => $site->phone,
                'tax_no' => $site->tax_no,
                'is_active' => (bool) $site->is_active,
            ],
        ]);
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
            'data' => [
                'id' => $site->id,
                'name' => $site->name,
                'address' => $site->address,
                'phone' => $site->phone,
                'tax_no' => $site->tax_no,
                'is_active' => (bool) $site->is_active,
            ],
        ]);
    }
}
