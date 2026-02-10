<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::where('site_id', auth()->user()->site_id)
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('management.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('management.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'tc_kimlik' => $request->tc_kimlik,
            'password' => Hash::make($request->password),
            'site_id' => auth()->user()->site_id,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('management.users.index')
            ->with('success', 'Kullanici olusturuldu.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $user->load('apartments');

        $availableApartments = Apartment::where('site_id', auth()->user()->site_id)
            ->where('is_active', true)
            ->whereDoesntHave('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('block')
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        return view('management.users.edit', compact('user', 'availableApartments'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'tc_kimlik' => $request->tc_kimlik,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return redirect()->route('management.users.index')
            ->with('success', 'Kullanici guncellendi.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kendinizi silemezsiniz.');
        }

        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('management.users.index')
            ->with('success', 'Kullanici silindi.');
    }

    public function addApartment(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $siteId = auth()->user()->site_id;

        $data = $request->validate([
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')
                    ->where(fn ($query) => $query->where('site_id', $siteId)->whereNull('deleted_at')),
            ],
            'relation_type' => ['required', 'in:owner,tenant'],
            'start_date' => ['nullable', 'date'],
        ]);

        $user->apartments()->syncWithoutDetaching([
            $data['apartment_id'] => [
                'relation_type' => $data['relation_type'],
                'start_date' => $data['start_date'] ?? now()->toDateString(),
            ],
        ]);

        return back()->with('success', 'Daire iliskisi eklendi.');
    }

    public function removeApartment(User $user, Apartment $apartment)
    {
        $this->authorize('update', $user);

        if ($apartment->site_id !== auth()->user()->site_id) {
            abort(404);
        }

        $user->apartments()->detach($apartment->id);

        return back()->with('success', 'Daire iliskisi kaldirildi.');
    }
}