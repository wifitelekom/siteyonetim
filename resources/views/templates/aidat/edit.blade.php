@extends('layouts.app')
@section('title', 'Aidat Şablonu Düzenle')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Aidat Şablonu Düzenle</h2>
    <a href="{{ route('templates.aidat.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-4xl mx-auto">
    <div class="p-8">
        <form method="POST" action="{{ route('templates.aidat.update', $template) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                <div>
                     <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Şablon Adı <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('name') border-red-300 bg-red-50 @enderror" id="name" name="name" value="{{ old('name', $template->name) }}" required>
                    @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="account_id" class="block text-sm font-medium text-gray-700 mb-2">Hesap <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="account_id" id="account_id" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none @error('account_id') border-red-300 bg-red-50 @enderror" required>
                             <option value="">Seçiniz</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id', $template->account_id) == $account->id ? 'selected' : '' }}>{{ $account->full_name }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                    @error('account_id')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                 <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Tutar (₺) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" class="w-full pl-8 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('amount') border-red-300 bg-red-50 @enderror" id="amount" name="amount" value="{{ old('amount', $template->amount) }}" required>
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium">₺</span>
                    </div>
                    @error('amount')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="due_day" class="block text-sm font-medium text-gray-700 mb-2">Vade Günü (1-28) <span class="text-red-500">*</span></label>
                    <input type="number" min="1" max="28" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('due_day') border-red-300 bg-red-50 @enderror" id="due_day" name="due_day" value="{{ old('due_day', $template->due_day) }}" required>
                    @error('due_day')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                <div>
                     <label for="scope" class="block text-sm font-medium text-gray-700 mb-2">Kapsam <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="scope" id="scope" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none @error('scope') border-red-300 bg-red-50 @enderror" required>
                             <option value="all" {{ old('scope', $template->scope) == 'all' ? 'selected' : '' }}>Tüm Daireler</option>
                            <option value="selected" {{ old('scope', $template->scope) == 'selected' ? 'selected' : '' }}>Seçili Daireler</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                     @error('scope')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div id="apartmentSelection" class="mb-6 p-6 bg-gray-50 rounded-xl border border-gray-100 hidden">
                <label class="block text-sm font-bold text-gray-800 mb-4">Daireler</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($apartments as $apt)
                    <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-[var(--primary)] transition-colors group">
                        <div class="relative flex items-center justify-center">
                             <input type="checkbox" name="apartment_ids[]" value="{{ $apt->id }}" id="apt{{ $apt->id }}" class="peer sr-only" {{ in_array($apt->id, old('apartment_ids', $template->apartments->pluck('id')->toArray())) ? 'checked' : '' }}>
                             <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-[var(--primary)] peer-checked:border-[var(--primary)] transition-all"></div>
                             <span class="material-symbols-outlined text-white text-[16px] absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none">check</span>
                        </div>
                         <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-900 transition-colors">{{ $apt->full_label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            
            <div class="mb-8">
                 <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="sr-only peer" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[var(--primary)] rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]"></div>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-900">Aktif</span>
                </label>
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

@push('scripts')
<script>
    const scopeSelect = document.getElementById('scope');
    const apartmentSelection = document.getElementById('apartmentSelection');
    
    function toggleApartmentSelection() {
        if (scopeSelect.value === 'selected') {
            apartmentSelection.classList.remove('hidden');
        } else {
            apartmentSelection.classList.add('hidden');
        }
    }

    scopeSelect.addEventListener('change', toggleApartmentSelection);
    
    // Initial check
    toggleApartmentSelection();
</script>
@endpush
@endsection
