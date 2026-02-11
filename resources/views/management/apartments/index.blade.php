@extends('layouts.app')
@section('title', 'Daireler')
@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Daire Listesi</h2>
        <a href="{{ route('management.apartments.create') }}" class="px-4 py-2 bg-[var(--primary)] text-white rounded-full font-medium hover:opacity-90 transition-opacity flex items-center gap-2 text-sm shadow-sm">
            <span class="material-symbols-outlined text-lg">add</span>
            Yeni Daire
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($apartments as $apt)
            <div class="bg-white rounded-card border border-gray-100 p-5 shadow-sm hover:shadow-md hover:translate-y-[-2px] transition-all group relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4">
                     <span class="w-3 h-3 rounded-full {{ $apt->is_active ? 'bg-green-500' : 'bg-gray-300' }} block" title="{{ $apt->is_active ? 'Aktif' : 'Pasif' }}"></span>
                </div>
                
                <div class="flex flex-col items-center text-center mb-4">
                    <div class="w-16 h-16 rounded-2xl bg-[var(--primary)] text-white flex items-center justify-center text-2xl font-bold mb-3 shadow-sm">
                        {{ $apt->number }}
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Blok {{ $apt->block ?? '-' }}</h3>
                    <p class="text-sm text-gray-500">Kat: {{ $apt->floor }}</p>
                </div>

                <div class="space-y-3 border-t border-gray-100 pt-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400">Sakin:</span>
                        <span class="font-medium text-gray-700 truncate max-w-[120px]" title="{{ $apt->current_owner?->name ?? $apt->current_tenant?->name ?? '-' }}">
                            {{ $apt->current_owner?->name ?? $apt->current_tenant?->name ?? '-' }}
                        </span>
                    </div>
                     <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400">Tip:</span>
                        <span class="font-medium text-gray-700">{{ $apt->type ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400">Net Alan:</span>
                        <span class="font-medium text-gray-700">{{ $apt->m2 ? $apt->m2 . ' m²' : '-' }}</span>
                    </div>
                </div>
                
                <div class="mt-5 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity absolute inset-x-0 bottom-4">
                     <a href="{{ route('management.apartments.show', $apt) }}" class="w-9 h-9 rounded-full bg-white border border-gray-200 text-gray-500 hover:text-[var(--primary)] hover:border-[var(--primary)] flex items-center justify-center transition-colors shadow-sm" title="Sakinler">
                        <span class="material-symbols-outlined text-lg">group</span>
                    </a>
                    <a href="{{ route('management.apartments.edit', $apt) }}" class="w-9 h-9 rounded-full bg-white border border-gray-200 text-blue-500 hover:bg-blue-50 hover:border-blue-200 flex items-center justify-center transition-colors shadow-sm" title="Düzenle">
                        <span class="material-symbols-outlined text-lg">edit</span>
                    </a>
                    <button class="w-9 h-9 rounded-full bg-white border border-gray-200 text-red-500 hover:bg-red-50 hover:border-red-200 flex items-center justify-center transition-colors shadow-sm" onclick="deleteRecord('{{ route('management.apartments.destroy', $apt) }}')" title="Sil">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-card border border-gray-100">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <span class="material-symbols-outlined text-3xl">apartment</span>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Daire Bulunamadı</h3>
                <p class="text-gray-500 text-sm">Sistemde kayıtlı daire bulunmuyor.</p>
            </div>
        @endforelse
    </div>
@endsection