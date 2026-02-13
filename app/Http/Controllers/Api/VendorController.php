<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vendor::class);

        $query = Vendor::query()
            ->withCount('expenses')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nested) use ($search) {
                $nested->where('name', 'like', '%' . $search . '%')
                    ->orWhere('tax_no', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $vendors = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $vendors->through(fn (Vendor $vendor) => $this->mapVendor($vendor))->items(),
            'meta' => [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
            ],
        ]);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        $this->authorize('view', $vendor);

        $vendor->loadCount('expenses');

        return response()->json([
            'data' => $this->mapVendor($vendor),
        ]);
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        $this->authorize('create', Vendor::class);

        $vendor = Vendor::create($request->validated());
        $vendor->loadCount('expenses');

        return response()->json([
            'message' => 'Tedarikci olusturuldu.',
            'data' => $this->mapVendor($vendor),
        ], 201);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $this->authorize('update', $vendor);

        $vendor->update($request->validated());
        $vendor->refresh()->loadCount('expenses');

        return response()->json([
            'message' => 'Tedarikci guncellendi.',
            'data' => $this->mapVendor($vendor),
        ]);
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $this->authorize('delete', $vendor);

        if ($vendor->expenses()->exists()) {
            return response()->json([
                'message' => 'Bu tedarikciye bagli giderler var, silinemez.',
            ], 422);
        }

        $vendor->delete();

        return response()->json([
            'message' => 'Tedarikci silindi.',
        ]);
    }

    private function mapVendor(Vendor $vendor): array
    {
        return [
            'id' => $vendor->id,
            'name' => $vendor->name,
            'tax_no' => $vendor->tax_no,
            'phone' => $vendor->phone,
            'email' => $vendor->email,
            'address' => $vendor->address,
            'is_active' => (bool) $vendor->is_active,
            'expenses_count' => (int) ($vendor->expenses_count ?? 0),
        ];
    }
}
