@extends('layouts.app')
@section('title', 'Yeni Hesap')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Yeni Hesap</h2>
    <a href="{{ route('accounts.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-2xl mx-auto">
    <div class="p-8">
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf
            
            <div class="mb-5">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Hesap Kodu <span class="text-red-500">*</span></label>
                <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('code') border-red-300 bg-red-50 @enderror" id="code" name="code" value="{{ old('code') }}" required placeholder="Örn: 100">
                @error('code')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Hesap Adı <span class="text-red-500">*</span></label>
                <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('name') border-red-300 bg-red-50 @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Örn: Kasa Hesabı">
                @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-8">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tür <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="type" id="type" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none @error('type') border-red-300 bg-red-50 @enderror" required>
                        <option value="">Seçiniz</option>
                        <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Gelir</option>
                        <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Gider</option>
                        <option value="asset" {{ old('type') == 'asset' ? 'selected' : '' }}>Varlık</option>
                        <option value="liability" {{ old('type') == 'liability' ? 'selected' : '' }}>Borç</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
                @error('type')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
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
