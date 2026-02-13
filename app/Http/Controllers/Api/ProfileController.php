<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Mevcut sifre zorunludur.',
            'current_password.current_password' => 'Mevcut sifre yanlis.',
            'password.required' => 'Yeni sifre zorunludur.',
            'password.confirmed' => 'Sifre tekrari uyusmuyor.',
            'password.min' => 'Sifre en az 8 karakter olmali.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Sifreniz basariyla guncellendi.',
        ]);
    }
}
