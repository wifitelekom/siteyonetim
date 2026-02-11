@extends('layouts.app')
@section('title', 'Tedarikçi Düzenle')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Tedarikçi Düzenle</h2>
    <a href="{{ route('management.vendors.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-2xl mx-auto">
    <div class="p-8">
        <form method="POST" action="{{ route('management.vendors.update', $vendor) }}">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                <div>
                     <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Firma Adı <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('name') border-red-300 bg-red-50 @enderror" id="name" name="name" value="{{ old('name', $vendor->name) }}" required placeholder="Firma Adı">
                    @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="tax_no" class="block text-sm font-medium text-gray-700 mb-2">Vergi No</label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="tax_no" name="tax_no" value="{{ old('tax_no', $vendor->tax_no) }}" placeholder="Vergi Numarası">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                 <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="phone" name="phone" value="{{ old('phone', $vendor->phone) }}" placeholder="0212 XXX XX XX">
                </div>
                
                 <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                    <input type="email" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="email" name="email" value="{{ old('email', $vendor->email) }}" placeholder="info@firma.com">
                </div>
            </div>
            
            <div class="mb-8">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                <textarea class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all resize-none" id="address" name="address" rows="3" placeholder="Açık adres...">{{ old('address', $vendor->address) }}</textarea>
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
