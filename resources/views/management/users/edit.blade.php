@extends('layouts.app')
@section('title', 'Kullanıcı Düzenle')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Kullanıcı Düzenle</h2>
    <a href="{{ route('management.users.index') }}" class="px-4 py-2 text-gray-500 hover:bg-gray-50 rounded-full transition-colors flex items-center gap-2 text-sm font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Geri
    </a>
</div>

<div class="bg-white rounded-card border border-gray-100 shadow-sm max-w-4xl mx-auto mb-6">
    <div class="p-8">
        <form method="POST" action="{{ route('management.users.update', $user) }}">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                <div>
                     <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('name') border-red-300 bg-red-50 @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta <span class="text-red-500">*</span></label>
                    <input type="email" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('email') border-red-300 bg-red-50 @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                 <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Cep Telefonu</label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('phone') border-red-300 bg-red-50 @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="05XX XXX XX XX">
                    @error('phone')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="tc_kimlik" class="block text-sm font-medium text-gray-700 mb-2">TC Kimlik No</label>
                    <input type="text" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('tc_kimlik') border-red-300 bg-red-50 @enderror" id="tc_kimlik" name="tc_kimlik" value="{{ old('tc_kimlik', $user->tc_kimlik) }}" maxlength="11" placeholder="11 haneli TC Kimlik">
                    @error('tc_kimlik')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                     <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Şifre (boş bırakılırsa değişmez)</label>
                    <input type="password" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all @error('password') border-red-300 bg-red-50 @enderror" id="password" name="password">
                    @error('password')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>
                
                 <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Şifre Tekrar</label>
                    <input type="password" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" id="password_confirmation" name="password_confirmation">
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Rol <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="role" id="role" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none" required>
                            <option value="admin" {{ old('role', $user->roles->first()?->name) == 'admin' ? 'selected' : '' }}>Yönetici</option>
                            <option value="owner" {{ old('role', $user->roles->first()?->name) == 'owner' ? 'selected' : '' }}>Ev Sahibi</option>
                            <option value="tenant" {{ old('role', $user->roles->first()?->name) == 'tenant' ? 'selected' : '' }}>Kiracı</option>
                            <option value="vendor" {{ old('role', $user->roles->first()?->name) == 'vendor' ? 'selected' : '' }}>Tedarikçi</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
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
        <h4 class="text-lg font-bold text-gray-800">Bağlı Daireler</h4>
    </div>
    <div class="p-8">
        <form method="POST" action="{{ route('management.users.add-apartment', $user) }}" class="flex flex-col md:flex-row gap-4 mb-6">
            @csrf
            <div class="flex-1 relative">
                <select name="apartment_id" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none" required>
                    <option value="">Daire seç</option>
                    @foreach($availableApartments as $apartment)
                        <option value="{{ $apartment->id }}">{{ $apartment->full_label }}</option>
                    @endforeach
                </select>
                 <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
            
            <div class="w-full md:w-32 relative">
                <select name="relation_type" class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all appearance-none" required>
                    <option value="owner">Ev Sahibi</option>
                    <option value="tenant">Kiracı</option>
                </select>
                 <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
            
             <div class="w-full md:w-40 relative">
                <input type="date" name="start_date" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent outline-none transition-all" value="{{ now()->toDateString() }}">
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
                        <th class="pb-3 font-semibold text-gray-600 text-sm">Daire</th>
                        <th class="pb-3 font-semibold text-gray-600 text-sm">Tür</th>
                        <th class="pb-3 font-semibold text-gray-600 text-sm">Başlangıç</th>
                        <th class="pb-3 font-semibold text-gray-600 text-sm text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($user->apartments as $apartment)
                        <tr class="group hover:bg-gray-50 transition-colors">
                            <td class="py-3 text-gray-800 font-medium">{{ $apartment->full_label }}</td>
                            <td class="py-3">
                                 <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $apartment->pivot->relation_type === 'owner' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600' }}">
                                    {{ $apartment->pivot->relation_type === 'owner' ? 'Ev Sahibi' : 'Kiracı' }}
                                </span>
                            </td>
                            <td class="py-3 text-gray-500 text-sm">{{ $apartment->pivot->start_date }}</td>
                            <td class="py-3 text-right">
                                <form method="POST"
                                    action="{{ route('management.users.remove-apartment', [$user, $apartment]) }}"
                                    class="d-inline">
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
                            <td colspan="4" class="py-8 text-center text-gray-400 italic">Bağlı daire yok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection