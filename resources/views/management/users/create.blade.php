@extends('layouts.app')
@section('title', 'Yeni Kullanıcı')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Yeni Kullanıcı</h2>
    <a href="{{ route('management.users.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-4xl mx-auto">
    <div class="p-8">
        <form method="POST" action="{{ route('management.users.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                <div>
                     <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('name') border-red-300 bg-red-50 @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Ali Yılmaz">
                    @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta <span class="text-red-500">*</span></label>
                    <input type="email" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('email') border-red-300 bg-red-50 @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="ali@ornek.com">
                    @error('email')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                 <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Cep Telefonu</label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('phone') border-red-300 bg-red-50 @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="05XX XXX XX XX">
                    @error('phone')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="tc_kimlik" class="block text-sm font-medium text-gray-700 mb-2">TC Kimlik No</label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('tc_kimlik') border-red-300 bg-red-50 @enderror" id="tc_kimlik" name="tc_kimlik" value="{{ old('tc_kimlik') }}" maxlength="11" placeholder="11 haneli TC Kimlik">
                    @error('tc_kimlik')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                     <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Şifre <span class="text-red-500">*</span></label>
                    <input type="password" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('password') border-red-300 bg-red-50 @enderror" id="password" name="password" required>
                    @error('password')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Şifre Tekrar <span class="text-red-500">*</span></label>
                    <input type="password" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="password_confirmation" name="password_confirmation" required>
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Rol <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="role" id="role" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none @error('role') border-red-300 bg-red-50 @enderror" required>
                             <option value="">Seçiniz</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Yönetici</option>
                            <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Ev Sahibi</option>
                            <option value="tenant" {{ old('role') == 'tenant' ? 'selected' : '' }}>Kiracı</option>
                            <option value="vendor" {{ old('role') == 'vendor' ? 'selected' : '' }}>Tedarikçi</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                    @error('role')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            
             <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-[var(--primary)] text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-md shadow-teal-900/10 flex items-center gap-2">
                    <span class="material-symbols-outlined">check</span>
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>
@endsection