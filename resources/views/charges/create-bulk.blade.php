@extends('layouts.app')
@section('title', 'Toplu Tahakkuk')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Toplu Tahakkuk</h2>
    <a href="{{ route('charges.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-5xl mx-auto">
    <div class="p-8">
        <form method="POST" action="{{ route('charges.store-bulk') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                 <div>
                    <label for="account_id" class="block text-sm font-medium text-gray-700 mb-2">Hesap <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="account_id" id="account_id" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none @error('account_id') border-red-300 bg-red-50 @enderror" required>
                             <option value="">Seçiniz</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>{{ $account->full_name }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                    @error('account_id')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="charge_type" class="block text-sm font-medium text-gray-700 mb-2">Tür <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="charge_type" id="charge_type" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none" required>
                            <option value="aidat">Aidat</option>
                            <option value="other">Diğer</option>
                        </select>
                         <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                </div>
                
                 <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Dönem <span class="text-red-500">*</span></label>
                    <input type="month" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="period" name="period" value="{{ old('period', date('Y-m')) }}" required>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                 <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Vade Tarihi <span class="text-red-500">*</span></label>
                    <input type="date" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                </div>
                
                 <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Tutar (₺) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="amount" name="amount" value="{{ old('amount') }}" required>
                </div>
            </div>
            
             <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Daireler <span class="text-red-500">*</span></label>
                
                 <div class="flex items-center mb-4">
                    <label class="flex items-center cursor-pointer select-none text-sm font-bold text-[var(--primary)]">
                        <div class="relative">
                            <input type="checkbox" id="select-all" class="sr-only">
                            <div class="w-5 h-5 border-2 border-[var(--primary)] rounded-md flex items-center justify-center transition-colors">
                                 <span class="material-symbols-outlined text-[16px] text-white opacity-0 transition-opacity" id="select-all-tick">check</span>
                            </div>
                        </div>
                        <span class="ml-2">Tümünü Seç</span>
                    </label>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200 max-h-[300px] overflow-y-auto custom-scrollbar">
                    @foreach($apartments as $apt)
                    <label class="flex items-center p-2 rounded-lg hover:bg-white hover:shadow-sm transition-all cursor-pointer border border-transparent hover:border-gray-100">
                        <div class="relative">
                            <input type="checkbox" name="apartment_ids[]" value="{{ $apt->id }}" class="sr-only row-checkbox" {{ in_array($apt->id, old('apartment_ids', [])) ? 'checked' : '' }}>
                             <div class="w-5 h-5 border-2 border-gray-300 rounded-md flex items-center justify-center transition-colors peer-checked:bg-[var(--primary)] peer-checked:border-[var(--primary)] checkbox-custom">
                                <span class="material-symbols-outlined text-[16px] text-white opacity-0 transition-opacity check-icon">check</span>
                            </div>
                        </div>
                        <span class="ml-2 text-sm text-gray-700">{{ $apt->full_label }}</span>
                    </label>
                    @endforeach
                </div>
                @error('apartment_ids')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="mb-8">
                 <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="description" name="description" value="{{ old('description') }}">
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-[var(--primary)] text-white rounded-xl font-medium hover:opacity-90 transition-all shadow-md shadow-teal-900/10 flex items-center gap-2">
                    <span class="material-symbols-outlined">library_add</span>
                    Toplu Oluştur
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .checkbox-custom + .check-icon {
        opacity: 0;
    }
    input:checked + .checkbox-custom {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    input:checked + .checkbox-custom .check-icon {
        opacity: 1;
    }
    /* Simple JS for Select All */
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const selectAllTick = document.getElementById('select-all-tick');
        const selectAllBox = selectAll.nextElementSibling;
        
        selectAll.addEventListener('change', function() {
            const checked = this.checked;
             if(checked) {
                selectAllBox.style.backgroundColor = 'var(--primary)';
                selectAllBox.style.borderColor = 'var(--primary)';
                selectAllTick.style.opacity = '1';
            } else {
                selectAllBox.style.backgroundColor = '';
                selectAllBox.style.borderColor = '';
                selectAllTick.style.opacity = '0';
            }

            checkboxes.forEach(cb => {
                cb.checked = checked;
                // Trigger change event manually if needed, or update styles
                // Since using CSS :checked, styles update automatically
            });
        });
    });
</script>
@endsection
