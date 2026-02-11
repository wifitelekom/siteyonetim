@extends('layouts.guest')
@section('title', 'Giris Yap')
@section('content')
    <div class="bg-white p-8 rounded-card border border-gray-100 shadow-xl shadow-gray-200/50">
        <h5 class="mb-6 text-center font-bold text-gray-800 text-lg">Hesabınıza giriş yapın</h5>

        @if(session('status'))
            <div class="mb-4 p-4 rounded-xl bg-green-50 text-green-600 text-sm border border-green-100">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-600 text-sm border border-red-100">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="identity" class="block text-sm font-medium text-gray-700 mb-2">E-posta, Telefon veya TC Kimlik</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">person</span>
                    <input type="text" 
                        class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('identity') border-red-300 bg-red-50 @enderror" 
                        id="identity"
                        name="identity" value="{{ old('identity') }}" required autofocus
                        placeholder="ornek@mail.com">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Şifre</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">key</span>
                    <input type="password" 
                        class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('password') border-red-300 bg-red-50 @enderror" 
                        id="password"
                        name="password" required
                        placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <input class="w-4 h-4 text-[var(--primary)] border-gray-300 rounded focus:ring-[var(--primary)]" type="checkbox" id="remember" name="remember">
                    <label class="ml-2 block text-sm text-gray-600" for="remember">Beni hatırla</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-[var(--primary)] hover:text-teal-700 hover:underline">Şifremi unuttum</a>
                @endif
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-[var(--primary)] text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-md shadow-teal-900/10 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">login</span>
                Giriş Yap
            </button>
        </form>
    </div>
    
    <div class="mt-6 text-center">
        <span class="text-gray-500 text-sm">Hesabınız yok mu?</span>
        @if (Route::has('register'))
            <a href="{{ route('register') }}" class="ml-1 text-[var(--primary)] font-bold hover:underline">Kayıt Ol</a>
        @endif
    </div>
@endsection