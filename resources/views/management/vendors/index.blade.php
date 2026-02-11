@extends('layouts.app')
@section('title', 'Tedarikçiler')
@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Tedarikçi Yönetimi</h2>
        <a href="{{ route('management.vendors.create') }}" class="px-4 py-2 bg-[var(--primary)] text-white rounded-full font-medium hover:opacity-90 transition-opacity flex items-center gap-2 text-sm shadow-sm">
            <span class="material-symbols-outlined text-lg">add_business</span>
            Yeni Tedarikçi
        </a>
    </div>

    <div class="bg-white rounded-card border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase font-bold text-gray-400">
                        <th class="px-6 py-4">Tedarikçi Adı</th>
                        <th class="px-6 py-4">Vergi No</th>
                        <th class="px-6 py-4">İletişim</th>
                        <th class="px-6 py-4 text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($vendors as $vendor)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                             <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[var(--secondary)]/10 flex items-center justify-center text-[var(--secondary)] font-bold">
                                        {{ substr($vendor->name, 0, 1) }}
                                    </div>
                                    <span class="font-semibold text-gray-800">{{ $vendor->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-mono text-sm">
                                {{ $vendor->tax_no ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2 text-sm text-gray-700">
                                        <span class="material-symbols-outlined text-sm text-gray-400">phone</span>
                                        {{ $vendor->phone ?? '-' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                                         <span class="material-symbols-outlined text-sm text-gray-400">mail</span>
                                        {{ $vendor->email ?? '-' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('management.vendors.edit', $vendor) }}" class="w-8 h-8 rounded-full bg-gray-50 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors" title="Düzenle">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                     <button class="w-8 h-8 rounded-full bg-gray-50 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors" onclick="deleteRecord('{{ route('management.vendors.destroy', $vendor) }}')" title="Sil">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">store_off</span>
                                    <p>Tedarikçi bulunmuyor</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection