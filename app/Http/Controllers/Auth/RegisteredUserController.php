<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $siteId = Site::query()->where('is_active', true)->value('id')
            ?? Site::query()->value('id');

        if (!$siteId) {
            return back()->withErrors([
                'email' => 'Kayit icin aktif bir site bulunamadi.',
            ])->onlyInput('email');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'site_id' => $siteId,
        ]);

        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('owner');
            } catch (\Throwable $e) {
                // Role seeding uygulanmadiysa kayit islemi yine de devam etsin.
            }
        }

        Auth::login($user);

        return redirect(route('dashboard'));
    }
}
