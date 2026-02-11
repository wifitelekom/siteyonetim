@extends('layouts.app')
@section('title', 'Site Ayarları')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Site Ayarları</h2>
    <a href="{{ route('dashboard') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-2xl mx-auto">
    <div class="p-8">
        <form method="POST" action="{{ route('management.site-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Site Adı <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">apartment</span>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('name') border-red-300 bg-red-50 @enderror"
                        value="{{ old('name', $site->name) }}"
                        required
                        placeholder="Örn: Güneş Sitesi"
                    >
                </div>
                @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-5">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">phone</span>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('phone') border-red-300 bg-red-50 @enderror"
                        value="{{ old('phone', $site->phone) }}"
                        placeholder="0212 ..."
                    >
                </div>
                @error('phone')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-8">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-3 text-gray-400">home_pin</span>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all resize-none @error('address') border-red-300 bg-red-50 @enderror"
                        placeholder="Adres detayları..."
                    >{{ old('address', $site->address) }}</textarea>
                </div>
                @error('address')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-[var(--primary)] text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-md shadow-teal-900/10 flex items-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Güncelle
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

