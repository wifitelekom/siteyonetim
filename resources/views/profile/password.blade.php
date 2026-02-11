@extends('layouts.app')
@section('title', 'Şifre Değiştir')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Şifre Değiştir</h2>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Geri
        </a>
    </div>

    <div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-2xl mx-auto">
        <div class="p-8">
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-5">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Mevcut Şifre</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">lock</span>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('current_password') border-red-300 bg-red-50 @enderror"
                            required
                        >
                    </div>
                    @error('current_password')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">key</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('password') border-red-300 bg-red-50 @enderror"
                            required
                        >
                    </div>
                    @error('password')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-8">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre (Tekrar)</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">lock_reset</span>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all"
                            required
                        >
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-[var(--primary)] text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-md shadow-teal-900/10 flex items-center gap-2">
                        <span class="material-symbols-outlined">save</span>
                        Şifreyi Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection