@extends('layouts.app')
@section('title', 'Yeni Daire')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Yeni Daire</h2>
    <a href="{{ route('management.apartments.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-2xl mx-auto">
    <div class="p-8">
        <form method="POST" action="{{ route('management.apartments.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-5">
                <div>
                     <label for="block" class="block text-sm font-medium text-gray-700 mb-2">Blok</label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('block') border-red-300 bg-red-50 @enderror" id="block" name="block" value="{{ old('block') }}" placeholder="A">
                     @error('block')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="floor" class="block text-sm font-medium text-gray-700 mb-2">Kat <span class="text-red-500">*</span></label>
                    <input type="number" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('floor') border-red-300 bg-red-50 @enderror" id="floor" name="floor" value="{{ old('floor') }}" required placeholder="1">
                    @error('floor')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="number" class="block text-sm font-medium text-gray-700 mb-2">Daire No <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('number') border-red-300 bg-red-50 @enderror" id="number" name="number" value="{{ old('number') }}" required placeholder="1">
                    @error('number')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label for="m2" class="block text-sm font-medium text-gray-700 mb-2">m2</label>
                    <input type="number" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="m2" name="m2" value="{{ old('m2') }}" placeholder="100">
                </div>
                
                 <div>
                    <label for="arsa_payi" class="block text-sm font-medium text-gray-700 mb-2">Arsa Payı</label>
                    <input type="number" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="arsa_payi" name="arsa_payi" value="{{ old('arsa_payi') }}" placeholder="50">
                </div>
                
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                    <label class="flex items-center cursor-pointer mt-2">
                        <div class="relative">
                            <input type="checkbox" name="is_active" id="is_active" value="1" class="sr-only peer" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[var(--primary)] rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-900">Aktif</span>
                    </label>
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
