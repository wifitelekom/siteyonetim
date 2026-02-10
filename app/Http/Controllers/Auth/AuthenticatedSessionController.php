<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        $request->session()->regenerateToken();

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'identity' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identity = trim($request->identity);
        $field = $this->resolveIdentityField($identity);

        if (!Auth::attempt([$field => $identity, 'password' => $request->password], $request->boolean('remember'))) {
            return back()->withErrors([
                'identity' => 'Girdiğiniz bilgiler hatalı.',
            ])->onlyInput('identity');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Girilen değere göre hangi sütunda aranacağını belirle.
     */
    private function resolveIdentityField(string $identity): string
    {
        // E-posta: @ içeriyorsa
        if (str_contains($identity, '@')) {
            return 'email';
        }

        // TC Kimlik: tam 11 hane rakam
        if (preg_match('/^\d{11}$/', $identity)) {
            return 'tc_kimlik';
        }

        // Aksi halde telefon
        return 'phone';
    }
}
