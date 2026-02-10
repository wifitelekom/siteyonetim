<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;

class VendorController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Vendor::class);

        $vendors = Vendor::orderBy('name')->get();

        return view('management.vendors.index', compact('vendors'));
    }

    public function create()
    {
        $this->authorize('create', Vendor::class);

        return view('management.vendors.create');
    }

    public function store(StoreVendorRequest $request)
    {
        $this->authorize('create', Vendor::class);

        Vendor::create($request->validated());

        return redirect()->route('management.vendors.index')
            ->with('success', 'Tedarikçi oluşturuldu.');
    }

    public function edit(Vendor $vendor)
    {
        $this->authorize('update', $vendor);

        return view('management.vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $this->authorize('update', $vendor);

        $vendor->update($request->validated());

        return redirect()->route('management.vendors.index')
            ->with('success', 'Tedarikçi güncellendi.');
    }

    public function destroy(Vendor $vendor)
    {
        $this->authorize('delete', $vendor);

        if ($vendor->expenses()->exists()) {
            return back()->with('error', 'Bu tedarikçiye bağlı giderler var, silinemez.');
        }

        $vendor->delete();

        return redirect()->route('management.vendors.index')
            ->with('success', 'Tedarikçi silindi.');
    }
}
