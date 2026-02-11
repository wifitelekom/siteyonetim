@extends('layouts.app')
@section('title', 'Yeni Kasa/Banka')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Yeni Kasa/Banka Hesabı</h2>
    <a href="{{ route('cash-accounts.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-2xl mx-auto">
    <div class="p-8">
        <form method="POST" action="{{ route('cash-accounts.store') }}">
            @csrf
            
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Hesap Adı <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">account_balance_wallet</span>
                    <input type="text" class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('name') border-red-300 bg-red-50 @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Örn: Akbank Vadeli">
                </div>
                @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tür <span class="text-red-500">*</span></label>
                     <div class="relative">
                        <select name="type" id="type" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none @error('type') border-red-300 bg-red-50 @enderror" required>
                            <option value="cash" {{ old('type') == 'cash' ? 'selected' : '' }}>Kasa (Nakit)</option>
                            <option value="bank" {{ old('type') == 'bank' ? 'selected' : '' }}>Banka</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                    @error('type')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                <div>
                     <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-2">Açılış Bakiyesi (₺) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('opening_balance') border-red-300 bg-red-50 @enderror" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}" required>
                    @error('opening_balance')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
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
