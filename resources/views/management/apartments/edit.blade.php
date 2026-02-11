@extends('layouts.app')
@section('title', 'Daire Düzenle')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Daire Düzenle</h2>
    <a href="{{ route('management.apartments.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-4xl mx-auto mb-6">
    <div class="p-8">
        <form method="POST" action="{{ route('management.apartments.update', $apartment) }}">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-5">
                <div>
                     <label for="block" class="block text-sm font-medium text-gray-700 mb-2">Blok</label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="block" name="block" value="{{ old('block', $apartment->block) }}" placeholder="A">
                </div>
                
                 <div>
                    <label for="floor" class="block text-sm font-medium text-gray-700 mb-2">Kat <span class="text-red-500">*</span></label>
                    <input type="number" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="floor" name="floor" value="{{ old('floor', $apartment->floor) }}" required placeholder="1">
                </div>
                
                 <div>
                    <label for="number" class="block text-sm font-medium text-gray-700 mb-2">Daire No <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="number" name="number" value="{{ old('number', $apartment->number) }}" required placeholder="1">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label for="m2" class="block text-sm font-medium text-gray-700 mb-2">m2</label>
                    <input type="number" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="m2" name="m2" value="{{ old('m2', $apartment->m2) }}" placeholder="100">
                </div>
                
                 <div>
                    <label for="arsa_payi" class="block text-sm font-medium text-gray-700 mb-2">Arsa Payı</label>
                    <input type="number" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="arsa_payi" name="arsa_payi" value="{{ old('arsa_payi', $apartment->arsa_payi) }}" placeholder="50">
                </div>
                
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                    <label class="flex items-center cursor-pointer mt-2">
                        <div class="relative">
                            <input type="checkbox" name="is_active" id="is_active" value="1" class="sr-only peer" {{ old('is_active', $apartment->is_active) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[var(--primary)] rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--primary)]"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-900">Aktif</span>
                    </label>
                </div>
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

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-4xl mx-auto">
    <div class="px-8 py-6 border-b border-gray-100">
        <h4 class="text-lg font-bold text-gray-800">Sakinler (Ev Sahibi / Kiracı)</h4>
    </div>
    <div class="p-8">
        <form method="POST" action="{{ route('management.apartments.add-resident', $apartment) }}" class="flex flex-col md:flex-row gap-4 mb-6">
            @csrf
            <div class="flex-1 relative">
                <select name="user_id" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none" required>
                    <option value="">Kullanıcı seç</option>
                    @foreach($availableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
            
            <div class="w-full md:w-48 relative">
                <select name="relation_type" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none" required>
                    <option value="owner">Ev Sahibi</option>
                    <option value="tenant">Kiracı</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
            
            <button type="submit" class="px-6 py-2 bg-[var(--primary)] text-white rounded-xl font-medium hover:opacity-90 transition-all flex items-center justify-center gap-2">
                 <span class="material-symbols-outlined">add</span>
                 Ekle
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="pb-3 font-semibold text-gray-600 text-sm">Ad</th>
                        <th class="pb-3 font-semibold text-gray-600 text-sm">Tür</th>
                        <th class="pb-3 font-semibold text-gray-600 text-sm">Başlangıç</th>
                        <th class="pb-3 font-semibold text-gray-600 text-sm text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($apartment->users as $user)
                        <tr class="group hover:bg-gray-50 transition-colors">
                            <td class="py-3 text-gray-800 font-medium">{{ $user->name }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $user->pivot->relation_type === 'owner' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600' }}">
                                    {{ $user->pivot->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiracı' }}
                                </span>
                            </td>
                            <td class="py-3 text-gray-500 text-sm">{{ $user->pivot->start_date }}</td>
                            <td class="py-3 text-right">
                                <form method="POST" action="{{ route('management.apartments.remove-resident', [$apartment, $user]) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-400 italic">Bu dairede kayıtlı sakin bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
