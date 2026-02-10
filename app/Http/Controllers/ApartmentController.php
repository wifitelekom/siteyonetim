<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApartmentController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Apartment::class);

        $apartments = Apartment::with(['owners', 'tenants'])
            ->orderBy('block')
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        return view('management.apartments.index', compact('apartments'));
    }

    public function create()
    {
        $this->authorize('create', Apartment::class);

        return view('management.apartments.create');
    }

    public function store(StoreApartmentRequest $request)
    {
        $this->authorize('create', Apartment::class);

        Apartment::create($request->validated());

        return redirect()->route('management.apartments.index')
            ->with('success', 'Daire başarıyla oluşturuldu.');
    }

    public function show(Apartment $apartment)
    {
        $this->authorize('view', $apartment);

        $apartment->load(['users', 'charges' => function ($q) {
            $q->orderByDesc('due_date')->limit(10);
        }]);

        $availableUsers = User::where('site_id', auth()->user()->site_id)
            ->whereDoesntHave('apartments', function ($q) use ($apartment) {
                $q->where('apartment_id', $apartment->id);
            })
            ->get();

        return view('management.apartments.show', compact('apartment', 'availableUsers'));
    }

    public function edit(Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        $apartment->load('users');

        $availableUsers = User::where('site_id', auth()->user()->site_id)
            ->whereDoesntHave('apartments', function ($q) use ($apartment) {
                $q->where('apartment_id', $apartment->id);
            })
            ->get();

        return view('management.apartments.edit', compact('apartment', 'availableUsers'));
    }

    public function update(UpdateApartmentRequest $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        $apartment->update($request->validated());

        return redirect()->route('management.apartments.index')
            ->with('success', 'Daire güncellendi.');
    }

    public function destroy(Apartment $apartment)
    {
        $this->authorize('delete', $apartment);

        if ($apartment->charges()->exists()) {
            return back()->with('error', 'Bu daireye bağlı tahakkuklar var, silinemez.');
        }

        $apartment->delete();

        return redirect()->route('management.apartments.index')
            ->with('success', 'Daire silindi.');
    }

    public function addResident(Request $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        $siteId = auth()->user()->site_id;

        $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('site_id', $siteId)),
            ],
            'relation_type' => 'required|in:owner,tenant',
            'start_date' => 'nullable|date',
        ]);

        $apartment->users()->syncWithoutDetaching([
            $request->user_id => [
                'relation_type' => $request->relation_type,
                'start_date' => $request->start_date ?? now(),
            ],
        ]);

        return back()->with('success', 'Sakin eklendi.');
    }

    public function removeResident(Apartment $apartment, User $user)
    {
        $this->authorize('update', $apartment);

        $apartment->users()->detach($user->id);

        return back()->with('success', 'Sakin kaldırıldı.');
    }
}
