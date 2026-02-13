<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identity' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $identity = trim($validated['identity']);
        $field = $this->resolveIdentityField($identity);
        $remember = (bool) ($validated['remember'] ?? false);

        if (!Auth::attempt([$field => $identity, 'password' => $validated['password']], $remember)) {
            return response()->json([
                'message' => 'Giris bilgileri hatali.',
                'errors' => [
                    'identity' => ['Giris bilgileri hatali.'],
                ],
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Giris basarili.',
            'data' => $this->mapAuthenticatedUser($request),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Kimlik dogrulamasi gerekli.',
            ], 401);
        }

        $user = $request->user();

        if (!$user->site_id && !$user->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Kullanici bir siteye atanmamis.',
            ], 403);
        }

        return response()->json([
            'data' => $this->mapAuthenticatedUser($request),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Cikis basarili.',
        ]);
    }

    private function resolveIdentityField(string $identity): string
    {
        if (str_contains($identity, '@')) {
            return 'email';
        }

        if (preg_match('/^\d{11}$/', $identity)) {
            return 'tc_kimlik';
        }

        return 'phone';
    }

    private function mapAuthenticatedUser(Request $request): array
    {
        $user = $request->user()->loadMissing('site');
        $permissions = $user->getAllPermissions()->pluck('name')->values()->all();
        $roles = $user->roles()->pluck('name')->values()->all();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'tc_kimlik' => $user->tc_kimlik,
                'site_id' => $user->site_id,
            ],
            'roles' => $roles,
            'permissions' => $permissions,
            'site' => $user->site ? [
                'id' => $user->site->id,
                'name' => $user->site->name,
            ] : null,
        ];
    }
}
