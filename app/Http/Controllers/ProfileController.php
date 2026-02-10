<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function passwordForm()
    {
        return view('profile.password');
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Mevcut şifre zorunludur.',
            'current_password.current_password' => 'Mevcut şifre yanlış.',
            'password.required' => 'Yeni şifre zorunludur.',
            'password.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Şifreniz başarıyla güncellendi.');
    }
}
