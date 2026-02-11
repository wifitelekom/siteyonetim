@extends('layouts.guest')
@section('title', 'Yeni Şifre')
@section('content')
<div class="bg-white p-8 rounded-card border border-gray-100 shadow-xl shadow-gray-200/50">
    <h5 class="mb-6 text-center font-bold text-gray-800 text-lg">Yeni şifre belirleyin</h5>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-600 text-sm border border-red-100">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">mail</span>
                <input
                    type="email"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('email') border-red-300 bg-red-50 @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                >
            </div>
            @error('email')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">key</span>
                <input
                    type="password"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('password') border-red-300 bg-red-50 @enderror"
                    id="password"
                    name="password"
                    required
                    placeholder="••••••••"
                >
            </div>
            @error('password')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre Tekrar</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">lock_reset</span>
                <input
                    type="password"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    placeholder="••••••••"
                >
            </div>
        </div>

        <button type="submit" class="w-full py-3 px-4 bg-[var(--primary)] text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-md shadow-teal-900/10 flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">shield_lock</span>
            Şifreyi Güncelle
        </button>
    </form>
</div>
<div class="mt-6 text-center">
    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-500 hover:text-[var(--primary)] transition-colors flex items-center justify-center gap-1">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Giriş ekranına dön
    </a>
</div>
@endsection
